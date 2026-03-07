-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 007.fun_read_historial_celular.sql
-- DESCRIPCIÓN: Retorna el historial completo de cambios
--              de responsable/estado para un celular dado.
--              Los datos están desnormalizados: no requiere
--              JOIN con tab_empleados (los nombres se guardaron
--              en el momento exacto del cambio por el trigger).
-- ============================================================

CREATE OR REPLACE FUNCTION fun_read_historial_celular(
    p_id_celular tab_celulares_historial.id_celular%TYPE
)
RETURNS TABLE (
    r_id_historial      INTEGER,
    r_linea_snapshot    VARCHAR,
    r_cod_nom_anterior  VARCHAR,
    r_nom_anterior      VARCHAR,
    r_cargo_anterior    VARCHAR,
    r_cod_nom_nuevo     VARCHAR,
    r_nom_nuevo         VARCHAR,
    r_cargo_nuevo       VARCHAR,
    r_estado_anterior   tipo_estado_celular,
    r_estado_nuevo      tipo_estado_celular,
    r_observacion       TEXT,
    r_fecha_cambio      TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        h.id_historial,
        h.linea_snapshot,
        h.cod_nom_anterior,
        h.nom_anterior,
        h.cargo_anterior,
        h.cod_nom_nuevo,
        h.nom_nuevo,
        h.cargo_nuevo,
        h.estado_anterior,
        h.estado_nuevo,
        h.observacion,
        h.fecha_cambio
    FROM   tab_celulares_historial h
    WHERE  h.id_celular = p_id_celular
    ORDER BY h.fecha_cambio DESC;
END;
$$ LANGUAGE plpgsql;
