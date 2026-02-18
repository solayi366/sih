-- ============================================================
-- FUNCIÓN: fun_read_activos_filtrado
-- Reemplaza fun_read_activos con soporte real de:
--   - Búsqueda de texto libre (serial, QR, hostname, tipo,
--     marca, modelo, responsable, área)
--   - Filtro por tipo de activo principal (laptop, computador, tablet...)
--   - Filtro por tipo de periférico asociado (mouse, teclado, etc.)
--   - Paginación
--
-- USO:
--   SELECT * FROM fun_read_activos_filtrado(1, 10, NULL, NULL, NULL);
--   SELECT * FROM fun_read_activos_filtrado(1, 10, 'dell', 'laptop', NULL);
--   SELECT * FROM fun_read_activos_filtrado(1, 10, NULL, NULL, 'mouse');
-- ============================================================

CREATE OR REPLACE FUNCTION fun_read_activos_filtrado(
    p_pagina               INTEGER,
    p_registros_por_pagina INTEGER,
    p_buscar               VARCHAR DEFAULT NULL,   -- texto libre
    p_tipo_principal       VARCHAR DEFAULT NULL,   -- 'laptop','computador','tablet' o NULL=todos
    p_tipo_periferico      VARCHAR DEFAULT NULL    -- 'mouse','teclado','monitor', etc. o NULL=todos
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
    total_registros BIGINT
) AS $$
DECLARE
    v_offset INTEGER;
    v_total  BIGINT;
    v_buscar VARCHAR;
BEGIN
    -- Normalizar búsqueda: null o vacío → null
    v_buscar := CASE WHEN TRIM(p_buscar) = '' THEN NULL ELSE TRIM(p_buscar) END;

    -- ── 1. CONTAR total con los mismos filtros (para paginación) ──────────
    SELECT COUNT(DISTINCT a.id_activo) INTO v_total
    FROM tab_activotec a
    INNER JOIN tab_tipos     t  ON a.id_tipoequi        = t.id_tipoequi
    INNER JOIN tab_marca     m  ON a.id_marca            = m.id_marca
    LEFT  JOIN tab_modelo    mo ON a.id_modelo           = mo.id_modelo
    LEFT  JOIN tab_empleados e  ON a.cod_nom_responsable = e.cod_nom
    LEFT  JOIN tab_area      ar ON e.id_area             = ar.id_area
    WHERE
        -- Solo activos principales activos
        a.activo = TRUE
        AND a.id_padre_activo IS NULL

        -- ── Filtro texto libre (busca en varios campos) ──────────────────
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

        -- ── Filtro tipo de activo principal ──────────────────────────────
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
            -- Cualquier otro valor filtra directo por nombre de tipo
            OR (p_tipo_principal NOT IN ('computador','laptop','tablet')
                AND t.nom_tipo ILIKE '%' || p_tipo_principal || '%')
        )

        -- ── Filtro por periférico asociado ────────────────────────────────
        AND (
            p_tipo_periferico IS NULL
            OR p_tipo_periferico = 'todos-peri'
            OR EXISTS (
                SELECT 1
                FROM tab_activotec   pa
                INNER JOIN tab_tipos pt ON pa.id_tipoequi = pt.id_tipoequi
                WHERE pa.activo = TRUE
                  AND pa.id_padre_activo = a.id_activo
                  AND (
                      (p_tipo_periferico = 'mouse'     AND (pt.nom_tipo ILIKE '%mouse%'     OR pt.nom_tipo ILIKE '%rat%n%'))
                   OR (p_tipo_periferico = 'teclado'   AND (pt.nom_tipo ILIKE '%teclado%'   OR pt.nom_tipo ILIKE '%keyboard%'))
                   OR (p_tipo_periferico = 'lector'    AND (pt.nom_tipo ILIKE '%lector%'    OR pt.nom_tipo ILIKE '%scanner%' OR pt.nom_tipo ILIKE '%esc%ner%'))
                   OR (p_tipo_periferico = 'monitor'   AND (pt.nom_tipo ILIKE '%monitor%'   OR pt.nom_tipo ILIKE '%pantalla%'))
                   OR (p_tipo_periferico = 'impresora' AND (pt.nom_tipo ILIKE '%impresora%' OR pt.nom_tipo ILIKE '%printer%'))
                   OR (pt.nom_tipo ILIKE '%' || p_tipo_periferico || '%')
                  )
            )
        );

    -- ── 2. Calcular offset ────────────────────────────────────────────────
    v_offset := (p_pagina - 1) * p_registros_por_pagina;

    -- ── 3. Retornar página de resultados ──────────────────────────────────
    RETURN QUERY
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
        NULL::VARCHAR(100),
        NULL::INTEGER,
        v_total
    FROM tab_activotec a
    INNER JOIN tab_tipos     t  ON a.id_tipoequi        = t.id_tipoequi
    INNER JOIN tab_marca     m  ON a.id_marca            = m.id_marca
    LEFT  JOIN tab_modelo    mo ON a.id_modelo           = mo.id_modelo
    LEFT  JOIN tab_empleados e  ON a.cod_nom_responsable = e.cod_nom
    LEFT  JOIN tab_area      ar ON e.id_area             = ar.id_area
    WHERE
        a.activo = TRUE
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
                WHERE pa.activo = TRUE
                  AND pa.id_padre_activo = a.id_activo
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
    OFFSET v_offset;
END;
$$ LANGUAGE plpgsql;