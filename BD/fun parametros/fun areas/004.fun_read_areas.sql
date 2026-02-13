CREATE OR REPLACE FUNCTION fun_read_areas()
RETURNS TABLE (
    r_id tab_area.id_area%TYPE, 
    r_nombre tab_area.nom_area%TYPE
) AS $$
BEGIN
    RETURN QUERY 
    SELECT id_area, nom_area
    FROM tab_area
    WHERE activo = TRUE
    ORDER BY nom_area ASC;
END;
$$ LANGUAGE plpgsql;