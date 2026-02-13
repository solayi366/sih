CREATE OR REPLACE FUNCTION fun_read_marcas()
RETURNS TABLE (
    r_id tab_marca.id_marca%TYPE, 
    r_nombre tab_marca.nom_marca%TYPE,
    r_tipo tab_tipos.nom_tipo%TYPE -- Nueva columna de retorno
) AS $$
BEGIN
    RETURN QUERY 
    SELECT 
        m.id_marca, 
        m.nom_marca,
        t.nom_tipo
    FROM tab_marca m
    INNER JOIN tab_tipos t ON m.id_tipoequi = t.id_tipoequi -- Vinculamos con tipos
    WHERE m.activo = TRUE
    ORDER BY t.nom_tipo ASC, m.nom_marca ASC;
END;
$$ LANGUAGE plpgsql;