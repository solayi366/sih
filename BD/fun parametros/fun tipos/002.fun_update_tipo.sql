CREATE OR REPLACE FUNCTION fun_update_tipo(
    p_id tab_tipos.id_tipoequi%TYPE,
    p_nombre tab_tipos.nom_tipo%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_tipos 
    SET nom_tipo = p_nombre
    WHERE id_tipoequi = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Tipo actualizado correctamente';
    ELSE
        msj := 'ERROR: No se encontró el registro o está inactivo';
    END IF;
    
    RETURN NEXT;

EXCEPTION WHEN OTHERS THEN
    filas_afectadas := 0;
    msj := 'ERROR CRÍTICO: ' || SQLERRM;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;