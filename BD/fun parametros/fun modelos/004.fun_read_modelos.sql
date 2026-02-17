CREATE OR REPLACE FUNCTION fun_read_modelos()
RETURNS TABLE (
    r_id_modelo tab_modelo.id_modelo%TYPE, 
    r_modelo tab_modelo.nom_modelo%TYPE,
    r_id_marca tab_marca.id_marca%TYPE, -- ID para el modal
    r_marca tab_marca.nom_marca%TYPE,
    r_id_tipo tab_tipos.id_tipoequi%TYPE, -- ID para el modal
    r_tipo tab_tipos.nom_tipo%TYPE
) AS $$
BEGIN
    RETURN QUERY 
    SELECT 
        m.id_modelo, 
        m.nom_modelo, 
        ma.id_marca,
        ma.nom_marca, 
        t.id_tipoequi,
        t.nom_tipo
    FROM tab_modelo m
    INNER JOIN tab_marca ma ON m.id_marca = ma.id_marca
    INNER JOIN tab_tipos t ON m.id_tipoequi = t.id_tipoequi
    WHERE m.activo = TRUE
    ORDER BY ma.nom_marca ASC, m.nom_modelo ASC;
END;
$$ LANGUAGE plpgsql;