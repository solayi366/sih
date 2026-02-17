CREATE OR REPLACE FUNCTION fun_read_empleados()
RETURNS TABLE (
    r_codigo tab_empleados.cod_nom%TYPE, 
    r_nombre tab_empleados.nom_emple%TYPE,
    r_id_area INTEGER, -- Agregamos el ID para el modal
    r_area VARCHAR,
    r_estado BOOLEAN
) AS $$
BEGIN
    RETURN QUERY 
    SELECT 
        e.cod_nom, 
        e.nom_emple, 
        e.id_area, -- Recuperamos el ID real
        a.nom_area, 
        e.activo
    FROM tab_empleados e
    INNER JOIN tab_area a ON e.id_area = a.id_area
    WHERE e.activo = TRUE
    ORDER BY e.nom_emple ASC;
END;
$$ LANGUAGE plpgsql;