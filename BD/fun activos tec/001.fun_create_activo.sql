CREATE OR REPLACE FUNCTION fun_create_activo(
    p_serial tab_activotec.serial%TYPE,
    p_codigo_qr tab_activotec.codigo_qr%TYPE,
    p_hostname tab_activotec.hostname%TYPE,
    p_referencia tab_activotec.referencia%TYPE,
    p_mac tab_activotec.mac_activo%TYPE,
    p_ip tab_activotec.ip_equipo%TYPE,
    p_id_tipo tab_activotec.id_tipoequi%TYPE,
    p_id_marca tab_activotec.id_marca%TYPE,
    p_id_modelo tab_activotec.id_modelo%TYPE,
    p_estado tab_activotec.estado%TYPE,
    p_responsable tab_activotec.cod_nom_responsable%TYPE,
    p_id_padre tab_activotec.id_padre_activo%TYPE DEFAULT NULL
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
DECLARE
    v_resp_final tab_activotec.cod_nom_responsable%TYPE;
BEGIN
    v_resp_final := p_responsable;

    -- Lógica de Herencia de Responsable
    IF p_id_padre IS NOT NULL AND v_resp_final IS NULL THEN
        SELECT cod_nom_responsable INTO v_resp_final 
        FROM tab_activotec WHERE id_activo = p_id_padre;
    END IF;

    -- Validación: No puede quedar huérfano de responsable
    IF v_resp_final IS NULL THEN
        id_res := -3;
        msj := 'ERROR: El activo debe tener un responsable o un padre con responsable asignado.';
        RETURN NEXT;
        RETURN;
    END IF;

    INSERT INTO tab_activotec (
        serial, codigo_qr, hostname, referencia, mac_activo, 
        ip_equipo, id_tipoequi, id_marca, id_modelo, estado, 
        cod_nom_responsable, id_padre_activo, activo
    )
    VALUES (
        p_serial, p_codigo_qr, p_hostname, p_referencia, p_mac, 
        p_ip, p_id_tipo, p_id_marca, p_id_modelo, p_estado, 
        v_resp_final, p_id_padre, TRUE
    )
    RETURNING id_activo, 'SUCCESS: Activo registrado correctamente'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        id_res := 0; msj := 'ERROR: Serial o Código QR ya existen.'; RETURN NEXT;
    WHEN foreign_key_violation THEN
        id_res := -1; msj := 'ERROR: Alguna referencia (Tipo/Marca/Modelo/Empleado) no existe.'; RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -2; msj := 'ERROR CRÍTICO: ' || SQLERRM; RETURN NEXT;
END;
$$ LANGUAGE plpgsql;