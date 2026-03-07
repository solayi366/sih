-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 008.fun_get_dashboard_celulares.sql
-- DESCRIPCIÓN: Estadísticas consolidadas para el dashboard
--              del módulo de celulares. Retorna conteos por
--              estado, por marca y por área en formato JSON
--              listo para Chart.js.
-- ============================================================

CREATE OR REPLACE FUNCTION fun_get_dashboard_celulares()
RETURNS TABLE (
    total_celulares     BIGINT,
    asignados           BIGINT,
    en_reposicion       BIGINT,
    en_reasignacion     BIGINT,
    de_baja             BIGINT,
    json_estados        JSON,
    json_marcas         JSON,
    json_areas          JSON
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM tab_celulares WHERE activo = TRUE),
        (SELECT COUNT(*) FROM tab_celulares WHERE activo = TRUE AND estado = 'ASIGNADO'),
        (SELECT COUNT(*) FROM tab_celulares WHERE activo = TRUE AND estado = 'EN REPOSICION'),
        (SELECT COUNT(*) FROM tab_celulares WHERE activo = TRUE AND estado = 'EN PROCESO DE REASIGNACION'),
        (SELECT COUNT(*) FROM tab_celulares WHERE activo = TRUE AND estado = 'DE BAJA'),

        (SELECT COALESCE(json_agg(t), '[]'::JSON)
         FROM (
             SELECT estado::TEXT AS label, COUNT(*)::INT AS count
             FROM tab_celulares WHERE activo = TRUE
             GROUP BY estado ORDER BY count DESC
         ) t),

        (SELECT COALESCE(json_agg(t_row), '[]'::JSON)
         FROM (
             SELECT m.nom_marca AS label, COUNT(*)::INT AS count
             FROM tab_celulares c
             INNER JOIN tab_marcas_cel m ON c.id_marca_cel = m.id_marca_cel
             WHERE c.activo = TRUE
             GROUP BY m.nom_marca ORDER BY count DESC
         ) t_row),

        (SELECT COALESCE(json_agg(t_area), '[]'::JSON)
         FROM (
             SELECT a.nom_area AS label, COUNT(*)::INT AS count
             FROM tab_celulares c
             INNER JOIN tab_empleados e ON c.cod_nom_responsable = e.cod_nom
             INNER JOIN tab_area a ON e.id_area = a.id_area
             WHERE c.activo = TRUE
             GROUP BY a.nom_area ORDER BY count DESC
         ) t_area);
END;
$$ LANGUAGE plpgsql;
