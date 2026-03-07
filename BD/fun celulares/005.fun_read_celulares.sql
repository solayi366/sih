-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 005.fun_read_celulares.sql
-- DESCRIPCIÓN: Listado paginado con búsqueda de texto libre
--              y filtro por estado. Retorna datos completos
--              para la tabla principal del módulo.
--              NO incluye credenciales (PIN/PUK).
-- USO:
--   SELECT * FROM fun_read_celulares(1, 10, NULL, NULL);
--   SELECT * FROM fun_read_celulares(1, 10, 'samsung', NULL);
--   SELECT * FROM fun_read_celulares(1, 10, NULL, 'ASIGNADO');
--   SELECT * FROM fun_read_celulares(1, 10, 'juan', 'DE BAJA');
-- ============================================================

CREATE OR REPLACE FUNCTION fun_read_celulares(
    p_pagina               INTEGER,
    p_registros_por_pagina INTEGER,
    p_buscar               VARCHAR DEFAULT NULL,
    p_estado               VARCHAR DEFAULT NULL
)
RETURNS TABLE (
    r_id                INTEGER,
    r_linea             VARCHAR,
    r_imei              VARCHAR,
    r_id_marca          INTEGER,
    r_marca             VARCHAR,
    r_id_modelo         INTEGER,
    r_modelo            VARCHAR,
    r_responsable       VARCHAR,
    r_cod_nom           VARCHAR,
    r_cargo             VARCHAR,
    r_area              VARCHAR,
    r_estado            tipo_estado_celular,
    r_observaciones     TEXT,
    r_fecha_registro    TIMESTAMP,
    total_registros     BIGINT
) AS $$
DECLARE
    v_offset INTEGER;
    v_total  BIGINT;
    v_buscar VARCHAR;
BEGIN
    v_buscar := NULLIF(TRIM(p_buscar), '');

    -- ── 1. Contar total con los mismos filtros ────────────────────────────────
    SELECT COUNT(*) INTO v_total
    FROM   tab_celulares       c
    INNER  JOIN tab_marcas_cel  m  ON c.id_marca_cel        = m.id_marca_cel
    INNER  JOIN tab_modelos_cel mo ON c.id_modelo_cel       = mo.id_modelo_cel
    LEFT   JOIN tab_empleados   e  ON c.cod_nom_responsable = e.cod_nom
    LEFT   JOIN tab_area        ar ON e.id_area             = ar.id_area
    WHERE  c.activo = TRUE
        AND (
            v_buscar IS NULL
            OR c.linea               ILIKE '%' || v_buscar || '%'
            OR c.imei                ILIKE '%' || v_buscar || '%'
            OR m.nom_marca           ILIKE '%' || v_buscar || '%'
            OR mo.nom_modelo         ILIKE '%' || v_buscar || '%'
            OR c.cod_nom_responsable ILIKE '%' || v_buscar || '%'
            OR e.nom_emple           ILIKE '%' || v_buscar || '%'
            OR c.cargo_responsable   ILIKE '%' || v_buscar || '%'
            OR ar.nom_area           ILIKE '%' || v_buscar || '%'
        )
        AND (
            p_estado IS NULL
            OR c.estado = p_estado::tipo_estado_celular
        );

    -- ── 2. Offset ─────────────────────────────────────────────────────────────
    v_offset := (p_pagina - 1) * p_registros_por_pagina;

    -- ── 3. Retornar página ────────────────────────────────────────────────────
    RETURN QUERY
    SELECT
        c.id_celular,
        c.linea,
        c.imei,
        c.id_marca_cel,
        m.nom_marca,
        c.id_modelo_cel,
        mo.nom_modelo,
        e.nom_emple,
        c.cod_nom_responsable,
        c.cargo_responsable,
        ar.nom_area,
        c.estado,
        c.observaciones,
        c.fecha_registro,
        v_total
    FROM   tab_celulares       c
    INNER  JOIN tab_marcas_cel  m  ON c.id_marca_cel        = m.id_marca_cel
    INNER  JOIN tab_modelos_cel mo ON c.id_modelo_cel       = mo.id_modelo_cel
    LEFT   JOIN tab_empleados   e  ON c.cod_nom_responsable = e.cod_nom
    LEFT   JOIN tab_area        ar ON e.id_area             = ar.id_area
    WHERE  c.activo = TRUE
        AND (
            v_buscar IS NULL
            OR c.linea               ILIKE '%' || v_buscar || '%'
            OR c.imei                ILIKE '%' || v_buscar || '%'
            OR m.nom_marca           ILIKE '%' || v_buscar || '%'
            OR mo.nom_modelo         ILIKE '%' || v_buscar || '%'
            OR c.cod_nom_responsable ILIKE '%' || v_buscar || '%'
            OR e.nom_emple           ILIKE '%' || v_buscar || '%'
            OR c.cargo_responsable   ILIKE '%' || v_buscar || '%'
            OR ar.nom_area           ILIKE '%' || v_buscar || '%'
        )
        AND (
            p_estado IS NULL
            OR c.estado = p_estado::tipo_estado_celular
        )
    ORDER BY c.fecha_registro DESC, c.id_celular DESC
    LIMIT  p_registros_por_pagina
    OFFSET v_offset;
END;
$$ LANGUAGE plpgsql;
