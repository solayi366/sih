-- ============================================================
-- MIGRACIÓN: Eliminar N+1 en listado de activos
--
-- Problema anterior:
--   activosController.php ejecutaba fun_read_activos_filtrado()
--   y luego, POR CADA ACTIVO, una consulta extra a
--   fun_read_perifericos_por_padre() → N+1 queries.
--   Con 10 activos por página = 11 queries mínimo.
--
-- Solución:
--   Una nueva función fun_read_activos_con_perifericos() que
--   devuelve los mismos datos + los periféricos ya agregados
--   como JSON en una sola columna, en UN solo viaje a la BD.
--
-- EJECUTAR:
--   psql -U postgres -d db_sih -f 007.fun_read_activos_con_perifericos.sql
-- ============================================================

CREATE OR REPLACE FUNCTION fun_read_activos_con_perifericos(
    p_pagina               INTEGER,
    p_registros_por_pagina INTEGER,
    p_buscar               VARCHAR DEFAULT NULL,
    p_tipo_principal       VARCHAR DEFAULT NULL,
    p_tipo_periferico      VARCHAR DEFAULT NULL
)
RETURNS TABLE (
    r_id            INTEGER,
    r_serial        VARCHAR,
    r_qr            VARCHAR,
    r_hostname      VARCHAR,
    r_tipo          VARCHAR,
    r_marca         VARCHAR,
    r_modelo        VARCHAR,
    r_estado        VARCHAR,
    r_responsable   VARCHAR,
    r_area          VARCHAR,
    r_padre_ref     VARCHAR,
    r_id_padre      INTEGER,
    total_registros BIGINT,
    perifericos     JSON        -- ← NUEVO: array JSON con los periféricos del activo
) AS $$
DECLARE
    v_offset INTEGER;
    v_total  BIGINT;
    v_buscar VARCHAR;
BEGIN
    -- Normalizar búsqueda
    v_buscar := CASE WHEN TRIM(p_buscar) = '' THEN NULL ELSE TRIM(p_buscar) END;

    -- ── CTE base: aplica filtros una sola vez ─────────────────────────────
    -- Se usa para COUNT y para los datos, evitando duplicar el WHERE.
    -- ─────────────────────────────────────────────────────────────────────

    -- 1. Contar con la CTE
    WITH base AS (
        SELECT a.id_activo
        FROM tab_activotec a
        INNER JOIN tab_tipos     t  ON a.id_tipoequi        = t.id_tipoequi
        INNER JOIN tab_marca     m  ON a.id_marca            = m.id_marca
        LEFT  JOIN tab_modelo    mo ON a.id_modelo           = mo.id_modelo
        LEFT  JOIN tab_empleados e  ON a.cod_nom_responsable = e.cod_nom
        LEFT  JOIN tab_area      ar ON e.id_area             = ar.id_area
        WHERE
            a.activo            = TRUE
            AND a.id_padre_activo IS NULL
            -- Búsqueda libre
            AND (
                v_buscar IS NULL
                OR a.serial      ILIKE '%' || v_buscar || '%'
                OR a.codigo_qr   ILIKE '%' || v_buscar || '%'
                OR a.hostname    ILIKE '%' || v_buscar || '%'
                OR a.referencia  ILIKE '%' || v_buscar || '%'
                OR t.nom_tipo    ILIKE '%' || v_buscar || '%'
                OR m.nom_marca   ILIKE '%' || v_buscar || '%'
                OR mo.nom_modelo ILIKE '%' || v_buscar || '%'
                OR e.nom_emple   ILIKE '%' || v_buscar || '%'
                OR ar.nom_area   ILIKE '%' || v_buscar || '%'
            )
            -- Filtro tipo principal
            AND (
                p_tipo_principal IS NULL
                OR p_tipo_principal = 'todos'
                OR (p_tipo_principal = 'computador' AND (
                        t.nom_tipo ILIKE '%computador%'
                     OR t.nom_tipo ILIKE '%desktop%'
                     OR t.nom_tipo ILIKE '%pc%'
                ))
                OR (p_tipo_principal = 'laptop' AND (
                        t.nom_tipo ILIKE '%laptop%'
                     OR t.nom_tipo ILIKE '%port%til%'
                     OR t.nom_tipo ILIKE '%portatil%'
                ))
                OR (p_tipo_principal = 'tablet' AND (
                        t.nom_tipo ILIKE '%tablet%'
                     OR t.nom_tipo ILIKE '%ipad%'
                ))
                OR (p_tipo_principal NOT IN ('computador','laptop','tablet')
                    AND t.nom_tipo ILIKE '%' || p_tipo_principal || '%')
            )
            -- Filtro por periférico
            AND (
                p_tipo_periferico IS NULL
                OR p_tipo_periferico = 'todos-peri'
                OR EXISTS (
                    SELECT 1
                    FROM tab_activotec   pa
                    INNER JOIN tab_tipos pt ON pa.id_tipoequi = pt.id_tipoequi
                    WHERE pa.activo            = TRUE
                      AND pa.id_padre_activo   = a.id_activo
                      AND (
                          (p_tipo_periferico = 'mouse'     AND (pt.nom_tipo ILIKE '%mouse%'     OR pt.nom_tipo ILIKE '%rat%n%'))
                       OR (p_tipo_periferico = 'teclado'   AND (pt.nom_tipo ILIKE '%teclado%'   OR pt.nom_tipo ILIKE '%keyboard%'))
                       OR (p_tipo_periferico = 'lector'    AND (pt.nom_tipo ILIKE '%lector%'    OR pt.nom_tipo ILIKE '%scanner%' OR pt.nom_tipo ILIKE '%esc%ner%'))
                       OR (p_tipo_periferico = 'monitor'   AND (pt.nom_tipo ILIKE '%monitor%'   OR pt.nom_tipo ILIKE '%pantalla%'))
                       OR (p_tipo_periferico = 'impresora' AND (pt.nom_tipo ILIKE '%impresora%' OR pt.nom_tipo ILIKE '%printer%'))
                       OR (pt.nom_tipo ILIKE '%' || p_tipo_periferico || '%')
                      )
                )
            )
    )
    SELECT COUNT(*) INTO v_total FROM base;

    -- 2. Calcular offset
    v_offset := (p_pagina - 1) * p_registros_por_pagina;

    -- 3. Retornar página con periféricos como JSON en una sola query
    RETURN QUERY
    WITH base AS (
        SELECT a.id_activo
        FROM tab_activotec a
        INNER JOIN tab_tipos     t  ON a.id_tipoequi        = t.id_tipoequi
        INNER JOIN tab_marca     m  ON a.id_marca            = m.id_marca
        LEFT  JOIN tab_modelo    mo ON a.id_modelo           = mo.id_modelo
        LEFT  JOIN tab_empleados e  ON a.cod_nom_responsable = e.cod_nom
        LEFT  JOIN tab_area      ar ON e.id_area             = ar.id_area
        WHERE
            a.activo            = TRUE
            AND a.id_padre_activo IS NULL
            AND (
                v_buscar IS NULL
                OR a.serial      ILIKE '%' || v_buscar || '%'
                OR a.codigo_qr   ILIKE '%' || v_buscar || '%'
                OR a.hostname    ILIKE '%' || v_buscar || '%'
                OR a.referencia  ILIKE '%' || v_buscar || '%'
                OR t.nom_tipo    ILIKE '%' || v_buscar || '%'
                OR m.nom_marca   ILIKE '%' || v_buscar || '%'
                OR mo.nom_modelo ILIKE '%' || v_buscar || '%'
                OR e.nom_emple   ILIKE '%' || v_buscar || '%'
                OR ar.nom_area   ILIKE '%' || v_buscar || '%'
            )
            AND (
                p_tipo_principal IS NULL
                OR p_tipo_principal = 'todos'
                OR (p_tipo_principal = 'computador' AND (
                        t.nom_tipo ILIKE '%computador%'
                     OR t.nom_tipo ILIKE '%desktop%'
                     OR t.nom_tipo ILIKE '%pc%'
                ))
                OR (p_tipo_principal = 'laptop' AND (
                        t.nom_tipo ILIKE '%laptop%'
                     OR t.nom_tipo ILIKE '%port%til%'
                     OR t.nom_tipo ILIKE '%portatil%'
                ))
                OR (p_tipo_principal = 'tablet' AND (
                        t.nom_tipo ILIKE '%tablet%'
                     OR t.nom_tipo ILIKE '%ipad%'
                ))
                OR (p_tipo_principal NOT IN ('computador','laptop','tablet')
                    AND t.nom_tipo ILIKE '%' || p_tipo_principal || '%')
            )
            AND (
                p_tipo_periferico IS NULL
                OR p_tipo_periferico = 'todos-peri'
                OR EXISTS (
                    SELECT 1
                    FROM tab_activotec   pa
                    INNER JOIN tab_tipos pt ON pa.id_tipoequi = pt.id_tipoequi
                    WHERE pa.activo            = TRUE
                      AND pa.id_padre_activo   = a.id_activo
                      AND (
                          (p_tipo_periferico = 'mouse'     AND (pt.nom_tipo ILIKE '%mouse%'     OR pt.nom_tipo ILIKE '%rat%n%'))
                       OR (p_tipo_periferico = 'teclado'   AND (pt.nom_tipo ILIKE '%teclado%'   OR pt.nom_tipo ILIKE '%keyboard%'))
                       OR (p_tipo_periferico = 'lector'    AND (pt.nom_tipo ILIKE '%lector%'    OR pt.nom_tipo ILIKE '%scanner%' OR pt.nom_tipo ILIKE '%esc%ner%'))
                       OR (p_tipo_periferico = 'monitor'   AND (pt.nom_tipo ILIKE '%monitor%'   OR pt.nom_tipo ILIKE '%pantalla%'))
                       OR (p_tipo_periferico = 'impresora' AND (pt.nom_tipo ILIKE '%impresora%' OR pt.nom_tipo ILIKE '%printer%'))
                       OR (pt.nom_tipo ILIKE '%' || p_tipo_periferico || '%')
                      )
                )
            )
        ORDER BY a.id_activo DESC
        LIMIT  p_registros_por_pagina
        OFFSET v_offset
    )
    SELECT
        a.id_activo,
        a.serial,
        a.codigo_qr,
        a.hostname,
        t.nom_tipo,
        m.nom_marca,
        mo.nom_modelo,
        a.estado,
        e.nom_emple,
        ar.nom_area,
        NULL::VARCHAR,
        NULL::INTEGER,
        v_total,
        -- Periféricos como JSON array — un solo LEFT JOIN lateral
        COALESCE(
            (
                SELECT json_agg(
                    json_build_object(
                        'r_id',       p.id_activo,
                        'r_serial',   p.serial,
                        'r_qr',       p.codigo_qr,
                        'r_tipo',     pt.nom_tipo,
                        'r_marca',    pm.nom_marca,
                        'r_modelo',   pmo.nom_modelo,
                        'r_estado',   p.estado,
                        'r_referencia', p.referencia
                    )
                    ORDER BY pt.nom_tipo, p.id_activo
                )
                FROM tab_activotec p
                INNER JOIN tab_tipos  pt  ON p.id_tipoequi = pt.id_tipoequi
                INNER JOIN tab_marca  pm  ON p.id_marca    = pm.id_marca
                LEFT  JOIN tab_modelo pmo ON p.id_modelo   = pmo.id_modelo
                WHERE p.activo          = TRUE
                  AND p.id_padre_activo = a.id_activo
            ),
            '[]'::JSON
        )
    FROM base b
    INNER JOIN tab_activotec a  ON a.id_activo = b.id_activo
    INNER JOIN tab_tipos     t  ON a.id_tipoequi        = t.id_tipoequi
    INNER JOIN tab_marca     m  ON a.id_marca            = m.id_marca
    LEFT  JOIN tab_modelo    mo ON a.id_modelo           = mo.id_modelo
    LEFT  JOIN tab_empleados e  ON a.cod_nom_responsable = e.cod_nom
    LEFT  JOIN tab_area      ar ON e.id_area             = ar.id_area
    ORDER BY a.id_activo DESC;

END;
$$ LANGUAGE plpgsql;