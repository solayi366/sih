CREATE OR REPLACE FUNCTION fun_delete_empleado(
    p_cod_nom tab_empleados.cod_nom%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_empleados 
    SET activo = FALSE 
    WHERE cod_nom = p_cod_nom;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Empleado desactivado (Borrado Lógico)';
    ELSE
        msj := 'ERROR: No se encontró el código de nómina';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;