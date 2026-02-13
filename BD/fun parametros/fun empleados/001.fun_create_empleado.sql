CREATE OR REPLACE FUNCTION fun_create_empleado(
    p_cod_nom tab_empleados.cod_nom%TYPE,
    p_nombre tab_empleados.nom_emple%TYPE,
    p_id_area tab_empleados.id_area%TYPE
)
RETURNS TABLE (cod_res VARCHAR, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_empleados (cod_nom, nom_emple, id_area, activo)
    VALUES (p_cod_nom, p_nombre, p_id_area, TRUE)
    RETURNING cod_nom, 'SUCCESS: Empleado registrado correctamente'::TEXT INTO cod_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        cod_res := '0';
        msj := 'ERROR: El código de nómina "' || p_cod_nom || '" ya está asignado a otro empleado.';
        RETURN NEXT;
    WHEN foreign_key_violation THEN
        cod_res := '0';
        msj := 'ERROR: El ID de área proporcionado no existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        cod_res := '-1';
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;