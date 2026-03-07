CREATE OR REPLACE FUNCTION fun_get_dashboard_stats()
RETURNS TABLE (
    total_activos BIGINT,
    pendientes BIGINT,
    operativos BIGINT,
    atencion BIGINT,
    json_estados JSON,
    json_tipos JSON
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
        
        -- Cantidad por tipo de dispositivo (Tablet, Portátil, Computador, Todo en Uno)
        (SELECT COALESCE(json_agg(t_row), '[]'::json) FROM (
            SELECT ti.nom_tipo as label, count(*)::int as count
            FROM tab_activotec a
            INNER JOIN tab_tipos ti ON a.id_tipoequi = ti.id_tipoequi
            WHERE a.activo = TRUE
              AND LOWER(ti.nom_tipo) IN ('tablet', 'portatil', 'portátil', 'computador', 'todo en uno')
            GROUP BY ti.nom_tipo
            ORDER BY count DESC
        ) t_row) as j_tipos;
END;
$$ LANGUAGE plpgsql;