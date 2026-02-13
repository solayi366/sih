CREATE OR REPLACE FUNCTION fun_create_actualizacion(
    p_id_activo tab_actualizaciones.id_activo%TYPE,
    p_tipo_evento tab_actualizaciones.tipo_evento%TYPE,
    p_desc_evento tab_actualizaciones.desc_evento%TYPE,
    p_usuario tab_actualizaciones.usuario_sistema%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_actualizaciones (
        id_activo, 
        tipo_evento, 
        desc_evento, 
        usuario_sistema, 
        fecha
    )
    VALUES (
        p_id_activo, 
        p_tipo_evento, 
        p_desc_evento, 
        p_usuario, 
        CURRENT_TIMESTAMP
    )
    RETURNING id_evento, 'SUCCESS: Evento de actualización registrado'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN foreign_key_violation THEN
        id_res := 0;
        msj := 'ERROR: El ID de activo no existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;