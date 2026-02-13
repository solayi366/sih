CREATE OR REPLACE FUNCTION fun_update_marca(
    p_id tab_marca.id_marca%TYPE,
    p_nombre tab_marca.nom_marca%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_marca 
    SET nom_marca = p_nombre
    WHERE id_marca = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Marca actualizada correctamente';
    ELSE
        msj := 'ERROR: No se encontró la marca o está desactivada';
    END IF;
    
    RETURN NEXT;

EXCEPTION WHEN OTHERS THEN
    filas_afectadas := 0;
    msj := 'ERROR CRÍTICO: ' || SQLERRM;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;