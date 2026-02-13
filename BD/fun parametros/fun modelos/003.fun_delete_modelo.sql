CREATE OR REPLACE FUNCTION fun_delete_modelo(
    p_id tab_modelo.id_modelo%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_modelo 
    SET activo = FALSE 
    WHERE id_modelo = p_id;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Modelo desactivado';
    ELSE
        msj := 'ERROR: ID de modelo no encontrado';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;