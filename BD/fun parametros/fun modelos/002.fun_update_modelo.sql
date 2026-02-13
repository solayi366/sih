CREATE OR REPLACE FUNCTION fun_update_modelo(
    p_id tab_modelo.id_modelo%TYPE,
    p_nombre tab_modelo.nom_modelo%TYPE,
    p_id_marca tab_modelo.id_marca%TYPE,
    p_id_tipo tab_modelo.id_tipoequi%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_modelo 
    SET nom_modelo = p_nombre,
        id_marca = p_id_marca,
        id_tipoequi = p_id_tipo
    WHERE id_modelo = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Modelo actualizado correctamente';
    ELSE
        msj := 'ERROR: No se encontró el modelo o está desactivado';
    END IF;
    
    RETURN NEXT;

EXCEPTION 
    WHEN foreign_key_violation THEN
        filas_afectadas := 0;
        msj := 'ERROR: Los nuevos IDs de Marca o Tipo no son válidos.';
        RETURN NEXT;
    WHEN OTHERS THEN
        filas_afectadas := 0;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;