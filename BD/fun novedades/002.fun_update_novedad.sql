CREATE OR REPLACE FUNCTION fun_update_novedad(
    p_id tab_novedades.id_novedad%TYPE,
    p_nuevo_estado tab_novedades.estado_ticket%TYPE,
    p_nueva_desc tab_novedades.descripcion%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_novedades 
    SET estado_ticket = p_nuevo_estado,
        descripcion = p_nueva_desc
    WHERE id_novedad = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Ticket actualizado a estado: ' || p_nuevo_estado;
    ELSE
        msj := 'ERROR: No se encontró la novedad o está eliminada.';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;