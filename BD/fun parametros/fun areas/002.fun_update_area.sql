CREATE OR REPLACE FUNCTION fun_update_area(
    p_id tab_area.id_area%TYPE,
    p_nombre tab_area.nom_area%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_area 
    SET nom_area = p_nombre
    WHERE id_area = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Área actualizada correctamente';
    ELSE
        msj := 'ERROR: No se encontró el área o está desactivada';
    END IF;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        filas_afectadas := 0;
        msj := 'ERROR: Ya existe otra área con ese nombre.';
        RETURN NEXT;
    WHEN OTHERS THEN
        filas_afectadas := 0;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;