CREATE OR REPLACE FUNCTION fun_delete_area(
    p_id tab_area.id_area%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_area 
    SET activo = FALSE 
    WHERE id_area = p_id;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Área desactivada';
    ELSE
        msj := 'ERROR: ID de área no encontrado';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;