CREATE OR REPLACE FUNCTION fun_delete_marca(
    p_id tab_marca.id_marca%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_marca 
    SET activo = FALSE 
    WHERE id_marca = p_id;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Marca desactivada del sistema';
    ELSE
        msj := 'ERROR: El ID de marca no existe';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;