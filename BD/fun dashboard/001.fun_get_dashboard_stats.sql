
CREATE OR REPLACE FUNCTION fun_get_dashboard_stats()
RETURNS TABLE (
    total_activos BIGINT,
    pendientes BIGINT,
    operativos BIGINT,
    atencion BIGINT,
    json_estados JSON,
    json_marcas JSON
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        -- Conteos Directos
        (SELECT COUNT(*) FROM tab_activotec WHERE activo = TRUE) as total,
        (SELECT COUNT(*) FROM tab_novedades WHERE estado_ticket = 'ABIERTO' AND activo = TRUE) as pend,
        (SELECT COUNT(*) FROM tab_activotec WHERE estado = 'OPERATIVO' AND activo = TRUE) as oper,
        (SELECT COUNT(*) FROM tab_activotec WHERE estado IN ('REPARACION', 'FALLA', 'MALO') AND activo = TRUE) as aten,
        
        -- Agregación de Estados (Formato para Chart.js)
        (SELECT COALESCE(json_agg(t), '[]'::json) FROM (
            SELECT estado as label, count(*)::int as count 
            FROM tab_activotec WHERE activo = TRUE GROUP BY estado
        ) t) as j_estados,
        
        -- Agregación de Marcas (Top 5)
        (SELECT COALESCE(json_agg(m_row), '[]'::json) FROM (
            SELECT m.nom_marca as label, count(*)::int as count 
            FROM tab_activotec a 
            INNER JOIN tab_marca m ON a.id_marca = m.id_marca 
            WHERE a.activo = TRUE 
            GROUP BY m.nom_marca ORDER BY count DESC LIMIT 5
        ) m_row) as j_marcas;
END;
$$ LANGUAGE plpgsql;