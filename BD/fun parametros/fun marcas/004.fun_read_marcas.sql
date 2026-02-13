CREATE OR REPLACE FUNCTION fun_read_marcas()
RETURNS TABLE (
    r_id tab_marca.id_marca%TYPE, 
    r_nombre tab_marca.nom_marca%TYPE
) AS $$
BEGIN
    RETURN QUERY 
    SELECT id_marca, nom_marca
    FROM tab_marca
    WHERE activo = TRUE
    ORDER BY nom_marca ASC;
END;
$$ LANGUAGE plpgsql;