-- ============================================================
-- MIGRACIÓN 003 — Historial Detallado con snapshot del activo
-- Permite guardar una "fotografía" del estado del activo en
-- cada evento (creación, edición, novedad resuelta, etc.)
-- ============================================================

-- Añadir columna snapshot si no existe (JSON con campos del activo en ese momento)
ALTER TABLE tab_actualizaciones
    ADD COLUMN IF NOT EXISTS snapshot JSONB DEFAULT NULL;

-- ── Función para leer historial de un activo con paginación ──────────────────
CREATE OR REPLACE FUNCTION fun_read_historial_activo(
    p_id_activo INTEGER,
    p_pagina    INTEGER DEFAULT 1,
    p_limite    INTEGER DEFAULT 20
)
RETURNS TABLE (
    r_id_evento  INTEGER,
    r_fecha      TIMESTAMP,
    r_tipo       VARCHAR(50),
    r_descripcion TEXT,
    r_usuario    VARCHAR(50),
    r_snapshot   JSONB,
    total_registros BIGINT
) AS $$
DECLARE
    v_offset INTEGER;
    v_total  BIGINT;
BEGIN
    SELECT COUNT(*) INTO v_total
    FROM tab_actualizaciones
    WHERE id_activo = p_id_activo;

    v_offset := (p_pagina - 1) * p_limite;

    RETURN QUERY
    SELECT
        a.id_evento,
        a.fecha,
        a.tipo_evento,
        a.desc_evento,
        a.usuario_sistema,
        a.snapshot,
        v_total
    FROM tab_actualizaciones a
    WHERE a.id_activo = p_id_activo
    ORDER BY a.fecha DESC
    LIMIT p_limite OFFSET v_offset;
END;
$$ LANGUAGE plpgsql;

-- ── Función para insertar un evento con snapshot opcional ────────────────────
CREATE OR REPLACE FUNCTION fun_registrar_evento_activo(
    p_id_activo   INTEGER,
    p_tipo_evento VARCHAR(50),
    p_desc_evento TEXT,
    p_usuario     VARCHAR(50),
    p_snapshot    JSONB DEFAULT NULL
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_actualizaciones
        (id_activo, tipo_evento, desc_evento, usuario_sistema, snapshot, fecha)
    VALUES
        (p_id_activo, p_tipo_evento, p_desc_evento, p_usuario, p_snapshot, CURRENT_TIMESTAMP)
    RETURNING id_evento, 'SUCCESS'::TEXT INTO id_res, msj;

    RETURN NEXT;
EXCEPTION WHEN OTHERS THEN
    id_res := -1;
    msj := 'ERROR: ' || SQLERRM;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;
