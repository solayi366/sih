-- ============================================================
-- MIGRACIÓN 011 — Sistema de Campos Dinámicos por Tipo
-- Ejecutar en orden. Compatible con estructura existente.
-- ============================================================

-- ── 1. CATÁLOGO DE CAMPOS DISPONIBLES ────────────────────────────────────────
-- Define qué campos existen en el sistema (base + personalizados).
-- Los campos base tienen is_base = TRUE y no se pueden eliminar.
CREATE TABLE IF NOT EXISTS tab_campos (
    id_campo    SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL UNIQUE,       -- Nombre técnico/clave
    etiqueta    VARCHAR(150) NOT NULL,              -- Label que ve el usuario
    tipo_dato   VARCHAR(20)  NOT NULL DEFAULT 'texto',
                                                    -- texto | numero | booleano | fecha | lista
    icono       VARCHAR(50)  DEFAULT 'fa-tag',      -- FontAwesome class
    opciones    TEXT,                               -- JSON para tipo=lista: ["Op1","Op2"]
    is_base     BOOLEAN      DEFAULT FALSE,         -- Campo base del sistema
    activo      BOOLEAN      DEFAULT TRUE,
    orden       INTEGER      DEFAULT 99,
    creado_en   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ── 2. RELACIÓN TIPO ↔ CAMPOS (qué campos tiene cada tipo) ───────────────────
CREATE TABLE IF NOT EXISTS tab_tipo_campos (
    id          SERIAL PRIMARY KEY,
    id_tipoequi INTEGER NOT NULL,
    id_campo    INTEGER NOT NULL,
    requerido   BOOLEAN DEFAULT FALSE,
    activo      BOOLEAN DEFAULT TRUE,
    orden       INTEGER DEFAULT 99,
    CONSTRAINT fk_tc_tipo  FOREIGN KEY (id_tipoequi) REFERENCES tab_tipos(id_tipoequi) ON DELETE CASCADE,
    CONSTRAINT fk_tc_campo FOREIGN KEY (id_campo)    REFERENCES tab_campos(id_campo)   ON DELETE CASCADE,
    CONSTRAINT uq_tipo_campo UNIQUE (id_tipoequi, id_campo)
);

-- ── 3. VALORES DE CAMPOS EXTRA POR ACTIVO ────────────────────────────────────
CREATE TABLE IF NOT EXISTS tab_activo_campos_valores (
    id          SERIAL PRIMARY KEY,
    id_activo   INTEGER NOT NULL,
    id_campo    INTEGER NOT NULL,
    valor       TEXT,
    CONSTRAINT fk_acv_activo FOREIGN KEY (id_activo) REFERENCES tab_activotec(id_activo) ON DELETE CASCADE,
    CONSTRAINT fk_acv_campo  FOREIGN KEY (id_campo)  REFERENCES tab_campos(id_campo)     ON DELETE CASCADE,
    CONSTRAINT uq_activo_campo UNIQUE (id_activo, id_campo)
);

-- ── 4. DATOS INICIALES: Campos Base del sistema ───────────────────────────────
INSERT INTO tab_campos (nombre, etiqueta, tipo_dato, icono, is_base, orden) VALUES
    ('serial',      'Serial / S/N',   'texto',    'fa-barcode',         TRUE,  1),
    ('referencia',  'Referencia',     'texto',    'fa-tag',             TRUE,  2),
    ('hostname',    'Hostname',       'texto',    'fa-server',          TRUE,  3),
    ('ip_equipo',   'Dirección IP',   'texto',    'fa-network-wired',   TRUE,  4),
    ('mac_activo',  'MAC Address',    'texto',    'fa-wifi',            TRUE,  5),
    ('estado',      'Estado',         'lista',    'fa-circle-dot',      TRUE,  6)
ON CONFLICT (nombre) DO NOTHING;

-- Actualizar opciones del campo estado
UPDATE tab_campos SET opciones = '["Bueno","Malo","Reparacion","Baja"]' WHERE nombre = 'estado' AND is_base = TRUE;

-- ── 5. FUNCIÓN: leer campos de un tipo ────────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_get_campos_por_tipo(p_id_tipo INTEGER)
RETURNS TABLE (
    id_campo    INTEGER,
    nombre      VARCHAR(100),
    etiqueta    VARCHAR(150),
    tipo_dato   VARCHAR(20),
    icono       VARCHAR(50),
    opciones    TEXT,
    is_base     BOOLEAN,
    requerido   BOOLEAN,
    orden       INTEGER
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        c.id_campo,
        c.nombre,
        c.etiqueta,
        c.tipo_dato,
        c.icono,
        c.opciones,
        c.is_base,
        COALESCE(tc.requerido, FALSE),
        COALESCE(tc.orden, c.orden)
    FROM tab_tipo_campos tc
    INNER JOIN tab_campos c ON tc.id_campo = c.id_campo
    WHERE tc.id_tipoequi = p_id_tipo
      AND tc.activo = TRUE
      AND c.activo  = TRUE
    ORDER BY COALESCE(tc.orden, c.orden), c.id_campo;
END;
$$ LANGUAGE plpgsql;

-- ── 6. FUNCIÓN: leer todos los campos del catálogo ───────────────────────────
CREATE OR REPLACE FUNCTION fun_get_todos_los_campos()
RETURNS TABLE (
    id_campo  INTEGER,
    nombre    VARCHAR(100),
    etiqueta  VARCHAR(150),
    tipo_dato VARCHAR(20),
    icono     VARCHAR(50),
    opciones  TEXT,
    is_base   BOOLEAN,
    orden     INTEGER
) AS $$
BEGIN
    RETURN QUERY
    SELECT c.id_campo, c.nombre, c.etiqueta, c.tipo_dato, c.icono, c.opciones, c.is_base, c.orden
    FROM tab_campos c
    WHERE c.activo = TRUE
    ORDER BY c.is_base DESC, c.orden, c.id_campo;
END;
$$ LANGUAGE plpgsql;

-- ── 7. FUNCIÓN: guardar/quitar campo de un tipo ───────────────────────────────
CREATE OR REPLACE FUNCTION fun_toggle_campo_tipo(
    p_id_tipo   INTEGER,
    p_id_campo  INTEGER,
    p_activo    BOOLEAN,
    p_requerido BOOLEAN DEFAULT FALSE,
    p_orden     INTEGER DEFAULT 99
)
RETURNS TABLE (msj TEXT) AS $$
BEGIN
    IF p_activo THEN
        INSERT INTO tab_tipo_campos (id_tipoequi, id_campo, requerido, orden, activo)
        VALUES (p_id_tipo, p_id_campo, p_requerido, p_orden, TRUE)
        ON CONFLICT (id_tipoequi, id_campo)
        DO UPDATE SET activo = TRUE, requerido = p_requerido, orden = p_orden;
        msj := 'SUCCESS: Campo activado para este tipo';
    ELSE
        UPDATE tab_tipo_campos
        SET activo = FALSE
        WHERE id_tipoequi = p_id_tipo AND id_campo = p_id_campo;
        msj := 'SUCCESS: Campo desactivado para este tipo';
    END IF;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;

-- ── 8. FUNCIÓN: crear campo personalizado ────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_create_campo(
    p_nombre    VARCHAR(100),
    p_etiqueta  VARCHAR(150),
    p_tipo_dato VARCHAR(20),
    p_icono     VARCHAR(50),
    p_opciones  TEXT DEFAULT NULL
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_campos (nombre, etiqueta, tipo_dato, icono, opciones, is_base)
    VALUES (p_nombre, p_etiqueta, p_tipo_dato, p_icono, p_opciones, FALSE)
    RETURNING id_campo, 'SUCCESS: Campo creado correctamente' INTO id_res, msj;
    RETURN NEXT;
EXCEPTION
    WHEN unique_violation THEN
        id_res := 0; msj := 'ERROR: Ya existe un campo con ese nombre técnico'; RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1; msj := 'ERROR: ' || SQLERRM; RETURN NEXT;
END;
$$ LANGUAGE plpgsql;

-- ── 9. FUNCIÓN: guardar valores extra de un activo ───────────────────────────
CREATE OR REPLACE FUNCTION fun_save_valores_activo(
    p_id_activo INTEGER,
    p_valores   JSONB        -- {"id_campo": "valor", ...}
)
RETURNS TABLE (msj TEXT) AS $$
DECLARE
    v_key   TEXT;
    v_val   TEXT;
    v_id    INTEGER;
BEGIN
    FOR v_key, v_val IN SELECT * FROM jsonb_each_text(p_valores) LOOP
        v_id := v_key::INTEGER;
        INSERT INTO tab_activo_campos_valores (id_activo, id_campo, valor)
        VALUES (p_id_activo, v_id, v_val)
        ON CONFLICT (id_activo, id_campo)
        DO UPDATE SET valor = v_val;
    END LOOP;
    msj := 'SUCCESS: Valores guardados'; RETURN NEXT;
EXCEPTION WHEN OTHERS THEN
    msj := 'ERROR: ' || SQLERRM; RETURN NEXT;
END;
$$ LANGUAGE plpgsql;

-- ── 10. FUNCIÓN: leer valores extra de un activo ─────────────────────────────
CREATE OR REPLACE FUNCTION fun_get_valores_activo(p_id_activo INTEGER)
RETURNS TABLE (
    id_campo  INTEGER,
    nombre    VARCHAR(100),
    etiqueta  VARCHAR(150),
    tipo_dato VARCHAR(20),
    icono     VARCHAR(50),
    is_base   BOOLEAN,
    valor     TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT c.id_campo, c.nombre, c.etiqueta, c.tipo_dato, c.icono, c.is_base, v.valor
    FROM tab_activo_campos_valores v
    INNER JOIN tab_campos c ON v.id_campo = c.id_campo
    WHERE v.id_activo = p_id_activo
      AND c.is_base = FALSE   -- Solo campos personalizados, los base tienen columna propia
    ORDER BY c.orden, c.id_campo;
END;
$$ LANGUAGE plpgsql;