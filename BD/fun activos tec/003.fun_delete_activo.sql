CREATE OR REPLACE FUNCTION fun_delete_activo(p_id tab_activotec.id_activo%TYPE)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_activotec SET activo = FALSE WHERE id_activo = p_id;
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    msj := CASE WHEN filas_afectadas > 0 THEN 'SUCCESS: Desactivado' ELSE 'ERROR: No encontrado' END;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;