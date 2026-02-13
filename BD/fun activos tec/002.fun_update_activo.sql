CREATE OR REPLACE FUNCTION fun_update_activo(
    p_id tab_activotec.id_activo%TYPE,
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
    p_id_padre tab_activotec.id_padre_activo%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_activotec 
    SET serial = p_serial,
        codigo_qr = p_codigo_qr,
        hostname = p_hostname,
        referencia = p_referencia,
        mac_activo = p_mac,
        ip_equipo = p_ip,
        id_tipoequi = p_id_tipo,
        id_marca = p_id_marca,
        id_modelo = p_id_modelo,
        estado = p_estado,
        cod_nom_responsable = p_responsable,
        id_padre_activo = p_id_padre
    WHERE id_activo = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Activo actualizado.';
    ELSE
        msj := 'ERROR: Activo no encontrado.';
    END IF;
    
    RETURN NEXT;

EXCEPTION 
    WHEN OTHERS THEN
        filas_afectadas := 0; msj := 'ERROR: ' || SQLERRM; RETURN NEXT;
END;
$$ LANGUAGE plpgsql;