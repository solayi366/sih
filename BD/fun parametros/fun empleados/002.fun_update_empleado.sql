CREATE OR REPLACE FUNCTION fun_update_empleado(
    p_cod_nom tab_empleados.cod_nom%TYPE,
    p_nombre tab_empleados.nom_emple%TYPE,
    p_id_area tab_empleados.id_area%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_empleados 
    SET nom_emple = p_nombre,
        id_area = p_id_area
    WHERE cod_nom = p_cod_nom AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Información del empleado actualizada';
    ELSE
        msj := 'ERROR: Empleado no encontrado o está inactivo';
    END IF;
    
    RETURN NEXT;

EXCEPTION 
    WHEN foreign_key_violation THEN
        filas_afectadas := 0;
        msj := 'ERROR: El nuevo ID de área no es válido.';
        RETURN NEXT;
    WHEN OTHERS THEN
        filas_afectadas := 0;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;