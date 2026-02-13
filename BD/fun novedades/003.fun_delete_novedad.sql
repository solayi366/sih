CREATE OR REPLACE FUNCTION fun_delete_novedad(p_id tab_novedades.id_novedad%TYPE)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_novedades SET activo = FALSE WHERE id_novedad = p_id;
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    msj := CASE WHEN filas_afectadas > 0 THEN 'SUCCESS: Novedad ocultada.' ELSE 'ERROR: ID no encontrado.' END;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;