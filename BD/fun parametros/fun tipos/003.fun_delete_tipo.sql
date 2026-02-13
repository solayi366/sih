CREATE OR REPLACE FUNCTION fun_delete_tipo(
    p_id tab_tipos.id_tipoequi%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_tipos 
    SET activo = FALSE 
    WHERE id_tipoequi = p_id;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Tipo de dispositivo desactivado';
    ELSE
        msj := 'ERROR: ID no encontrado';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;