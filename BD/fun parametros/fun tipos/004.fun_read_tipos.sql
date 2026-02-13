CREATE OR REPLACE FUNCTION fun_read_tipos()
RETURNS TABLE (
    r_id tab_tipos.id_tipoequi%TYPE, 
    r_nombre tab_tipos.nom_tipo%TYPE
) AS $$
BEGIN
    RETURN QUERY 
    SELECT id_tipoequi, nom_tipo
    FROM tab_tipos
    WHERE activo = TRUE
    ORDER BY nom_tipo ASC;
END;
$$ LANGUAGE plpgsql;