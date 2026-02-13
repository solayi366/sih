CREATE OR REPLACE FUNCTION fun_create_novedad(
    p_cod_nom tab_empleados.cod_nom%TYPE,
    p_tipo_dano tab_novedades.tipo_dano%TYPE,
    p_descripcion tab_novedades.descripcion%TYPE,
    p_evidencia tab_novedades.evidencia_foto%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
DECLARE
    v_nombre_emple tab_empleados.nom_emple%TYPE;
    v_id_activo tab_activotec.id_activo%TYPE;
BEGIN
    -- 1. Buscamos el nombre del empleado
    SELECT nom_emple INTO v_nombre_emple 
    FROM tab_empleados 
    WHERE cod_nom = p_cod_nom AND activo = TRUE;

    IF v_nombre_emple IS NULL THEN
        id_res := 0;
        msj := 'ERROR: El código de nómina no corresponde a un empleado activo.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- 2. Buscamos el activo asignado (Priorizamos el equipo principal, no periféricos)
    SELECT id_activo INTO v_id_activo 
    FROM tab_activotec 
    WHERE cod_nom_responsable = p_cod_nom 
      AND activo = TRUE 
    ORDER BY id_padre_activo NULLS FIRST -- Esto trae el equipo padre si tiene varios
    LIMIT 1;

    IF v_id_activo IS NULL THEN
        id_res := -1;
        msj := 'ERROR: El empleado ' || v_nombre_emple || ' no tiene activos asignados para reportar.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- 3. Insertamos con la información recuperada automáticamente
    INSERT INTO tab_novedades (
        cedula_reportante, -- Usamos el código de nómina como identificador
        nombre_reportante, 
        id_activo, 
        tipo_dano, 
        descripcion, 
        evidencia_foto, 
        estado_ticket,
        activo
    )
    VALUES (
        p_cod_nom, 
        v_nombre_emple, 
        v_id_activo, 
        p_tipo_dano, 
        p_descripcion, 
        p_evidencia, 
        'ABIERTO', 
        TRUE
    )
    RETURNING id_novedad, 'SUCCESS: Ticket #'||id_novedad::TEXT||' creado para el equipo con ID '||v_id_activo::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION WHEN OTHERS THEN
    id_res := -2;
    msj := 'ERROR CRÍTICO: ' || SQLERRM;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;