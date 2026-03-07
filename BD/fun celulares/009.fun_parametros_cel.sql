-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 009.fun_parametros_cel.sql
-- DESCRIPCIÓN: CRUD completo para los dos catálogos gestionables
--              desde la pantalla de parámetros del admin:
--
--   MARCAS:   fun_create_marca_cel / fun_read_marcas_cel /
--             fun_update_marca_cel / fun_delete_marca_cel /
--             fun_restore_marca_cel
--
--   MODELOS:  fun_create_modelo_cel / fun_read_modelos_cel /
--             fun_read_modelos_por_marca (para select cascada) /
--             fun_update_modelo_cel / fun_delete_modelo_cel
--
-- Mismo patrón de retorno que el módulo TEC (áreas, tipos, etc.)
-- ============================================================


-- ╔══════════════════════════════════════════════════════════╗
-- ║                       MARCAS                            ║
-- ╚══════════════════════════════════════════════════════════╝

-- ── CREATE ────────────────────────────────────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_create_marca_cel(
    p_nom_marca tab_marcas_cel.nom_marca%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_marcas_cel (nom_marca)
    VALUES (TRIM(UPPER(p_nom_marca)))
    RETURNING id_marca_cel,
              'SUCCESS: Marca ' || TRIM(UPPER(p_nom_marca)) || ' registrada correctamente.'
    INTO id_res, msj;
    RETURN NEXT;
EXCEPTION
    WHEN unique_violation THEN
        id_res := 0;
        msj    := 'ERROR: La marca ' || TRIM(UPPER(p_nom_marca)) || ' ya existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj    := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;


-- ── READ (listado completo para parámetros y selects) ─────────────────────────
CREATE OR REPLACE FUNCTION fun_read_marcas_cel()
RETURNS TABLE (
    r_id        INTEGER,
    r_nombre    VARCHAR,
    r_modelos   BIGINT      -- Cantidad de modelos activos asociados (útil en pantalla de parámetros)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        m.id_marca_cel,
        m.nom_marca,
        COUNT(mo.id_modelo_cel)
    FROM   tab_marcas_cel  m
    LEFT   JOIN tab_modelos_cel mo ON mo.id_marca_cel = m.id_marca_cel
                                  AND mo.activo       = TRUE
    WHERE  m.activo = TRUE
    GROUP BY m.id_marca_cel, m.nom_marca
    ORDER BY m.nom_marca ASC;
END;
$$ LANGUAGE plpgsql;


-- ── UPDATE ────────────────────────────────────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_update_marca_cel(
    p_id        tab_marcas_cel.id_marca_cel%TYPE,
    p_nom_marca tab_marcas_cel.nom_marca%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_marcas_cel
    SET    nom_marca = TRIM(UPPER(p_nom_marca))
    WHERE  id_marca_cel = p_id
      AND  activo       = TRUE;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    msj := CASE
        WHEN filas_afectadas > 0 THEN 'SUCCESS: Marca actualizada correctamente.'
        ELSE 'ERROR: No se encontró la marca o está inactiva.'
    END;
    RETURN NEXT;
EXCEPTION
    WHEN unique_violation THEN
        filas_afectadas := 0;
        msj := 'ERROR: Ya existe otra marca con ese nombre.';
        RETURN NEXT;
    WHEN OTHERS THEN
        filas_afectadas := 0;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;


-- ── DELETE (soft) ─────────────────────────────────────────────────────────────
-- Bloquea si la marca tiene celulares activos registrados.
-- Permite desactivar aunque tenga modelos (los modelos también se desactivan).
CREATE OR REPLACE FUNCTION fun_delete_marca_cel(
    p_id tab_marcas_cel.id_marca_cel%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    -- Bloquear si hay celulares activos usando esta marca
    IF EXISTS (
        SELECT 1 FROM tab_celulares
        WHERE  id_marca_cel = p_id AND activo = TRUE
    ) THEN
        filas_afectadas := 0;
        msj := 'ERROR: No se puede desactivar la marca porque tiene celulares activos asociados.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- Desactivar modelos de esta marca en cascada
    UPDATE tab_modelos_cel SET activo = FALSE WHERE id_marca_cel = p_id;

    -- Desactivar la marca
    UPDATE tab_marcas_cel SET activo = FALSE WHERE id_marca_cel = p_id;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    msj := CASE
        WHEN filas_afectadas > 0 THEN 'SUCCESS: Marca y sus modelos desactivados correctamente.'
        ELSE 'ERROR: No se encontró la marca.'
    END;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;


-- ── RESTORE (reactivar marca desactivada) ────────────────────────────────────
-- Reactiva la marca. Los modelos que tenía NO se reactivan automáticamente
-- porque pueden haber sido desactivados individualmente por otras razones.
-- El admin los gestiona desde la pantalla de parámetros de modelos.
CREATE OR REPLACE FUNCTION fun_restore_marca_cel(
    p_id tab_marcas_cel.id_marca_cel%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
DECLARE
    v_nom_marca tab_marcas_cel.nom_marca%TYPE;
BEGIN
    -- Verificar que la marca existe (incluso si está inactiva)
    SELECT nom_marca INTO v_nom_marca
    FROM   tab_marcas_cel
    WHERE  id_marca_cel = p_id;

    IF v_nom_marca IS NULL THEN
        filas_afectadas := 0;
        msj := 'ERROR: No se encontró ninguna marca con ID ' || p_id || '.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- Verificar que realmente está desactivada
    IF EXISTS (
        SELECT 1 FROM tab_marcas_cel
        WHERE  id_marca_cel = p_id AND activo = TRUE
    ) THEN
        filas_afectadas := 0;
        msj := 'ERROR: La marca ' || v_nom_marca || ' ya está activa.';
        RETURN NEXT;
        RETURN;
    END IF;

    UPDATE tab_marcas_cel
    SET    activo = TRUE
    WHERE  id_marca_cel = p_id;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    msj := 'SUCCESS: Marca ' || v_nom_marca || ' reactivada. Sus modelos deben reactivarse manualmente desde parámetros de modelos.';
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;


-- ╔══════════════════════════════════════════════════════════╗
-- ║                      MODELOS                            ║
-- ╚══════════════════════════════════════════════════════════╝

-- ── CREATE ────────────────────────────────────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_create_modelo_cel(
    p_id_marca_cel  tab_modelos_cel.id_marca_cel%TYPE,
    p_nom_modelo    tab_modelos_cel.nom_modelo%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
DECLARE
    v_nom_marca tab_marcas_cel.nom_marca%TYPE;
BEGIN
    -- Validar que la marca existe y está activa
    SELECT nom_marca INTO v_nom_marca
    FROM   tab_marcas_cel
    WHERE  id_marca_cel = p_id_marca_cel AND activo = TRUE;

    IF v_nom_marca IS NULL THEN
        id_res := -1;
        msj    := 'ERROR: La marca con ID ' || p_id_marca_cel || ' no existe o está inactiva.';
        RETURN NEXT;
        RETURN;
    END IF;

    INSERT INTO tab_modelos_cel (id_marca_cel, nom_modelo)
    VALUES (p_id_marca_cel, TRIM(UPPER(p_nom_modelo)))
    RETURNING id_modelo_cel,
              'SUCCESS: Modelo ' || TRIM(UPPER(p_nom_modelo)) || ' creado para ' || v_nom_marca || '.'
    INTO id_res, msj;
    RETURN NEXT;

EXCEPTION
    WHEN unique_violation THEN
        id_res := 0;
        msj    := 'ERROR: El modelo ' || TRIM(UPPER(p_nom_modelo)) || ' ya existe para esa marca.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -2;
        msj    := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;


-- ── READ: todos los modelos activos (para la tabla de parámetros) ─────────────
CREATE OR REPLACE FUNCTION fun_read_modelos_cel()
RETURNS TABLE (
    r_id_modelo     INTEGER,
    r_id_marca      INTEGER,
    r_nom_marca     VARCHAR,
    r_nom_modelo    VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        mo.id_modelo_cel,
        mo.id_marca_cel,
        m.nom_marca,
        mo.nom_modelo
    FROM   tab_modelos_cel  mo
    INNER  JOIN tab_marcas_cel m ON mo.id_marca_cel = m.id_marca_cel
    WHERE  mo.activo = TRUE
      AND  m.activo  = TRUE
    ORDER BY m.nom_marca ASC, mo.nom_modelo ASC;
END;
$$ LANGUAGE plpgsql;


-- ── READ: modelos filtrados por marca (para el select cascada del formulario) ──
-- Llamada en JS cuando el usuario selecciona una marca:
-- fetch('?action=modelos_por_marca&id_marca=X') → retorna esta función.
CREATE OR REPLACE FUNCTION fun_read_modelos_por_marca(
    p_id_marca_cel tab_modelos_cel.id_marca_cel%TYPE
)
RETURNS TABLE (
    r_id_modelo     INTEGER,
    r_nom_modelo    VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        mo.id_modelo_cel,
        mo.nom_modelo
    FROM   tab_modelos_cel mo
    WHERE  mo.id_marca_cel = p_id_marca_cel
      AND  mo.activo       = TRUE
    ORDER BY mo.nom_modelo ASC;
END;
$$ LANGUAGE plpgsql;


-- ── UPDATE ────────────────────────────────────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_update_modelo_cel(
    p_id_modelo     tab_modelos_cel.id_modelo_cel%TYPE,
    p_id_marca_cel  tab_modelos_cel.id_marca_cel%TYPE,
    p_nom_modelo    tab_modelos_cel.nom_modelo%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    -- Validar que la nueva marca existe
    IF NOT EXISTS (
        SELECT 1 FROM tab_marcas_cel
        WHERE  id_marca_cel = p_id_marca_cel AND activo = TRUE
    ) THEN
        filas_afectadas := 0;
        msj := 'ERROR: La marca con ID ' || p_id_marca_cel || ' no existe o está inactiva.';
        RETURN NEXT;
        RETURN;
    END IF;

    UPDATE tab_modelos_cel
    SET    id_marca_cel = p_id_marca_cel,
           nom_modelo   = TRIM(UPPER(p_nom_modelo))
    WHERE  id_modelo_cel = p_id_modelo
      AND  activo        = TRUE;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    msj := CASE
        WHEN filas_afectadas > 0 THEN 'SUCCESS: Modelo actualizado correctamente.'
        ELSE 'ERROR: No se encontró el modelo o está inactivo.'
    END;
    RETURN NEXT;
EXCEPTION
    WHEN unique_violation THEN
        filas_afectadas := 0;
        msj := 'ERROR: Ya existe ese modelo para la marca seleccionada.';
        RETURN NEXT;
    WHEN OTHERS THEN
        filas_afectadas := 0;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;


-- ── DELETE (soft) ─────────────────────────────────────────────────────────────
-- Bloquea si hay celulares activos usando el modelo.
CREATE OR REPLACE FUNCTION fun_delete_modelo_cel(
    p_id tab_modelos_cel.id_modelo_cel%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM tab_celulares
        WHERE  id_modelo_cel = p_id AND activo = TRUE
    ) THEN
        filas_afectadas := 0;
        msj := 'ERROR: No se puede desactivar el modelo porque tiene celulares activos asociados.';
        RETURN NEXT;
        RETURN;
    END IF;

    UPDATE tab_modelos_cel SET activo = FALSE WHERE id_modelo_cel = p_id;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    msj := CASE
        WHEN filas_afectadas > 0 THEN 'SUCCESS: Modelo desactivado correctamente.'
        ELSE 'ERROR: No se encontró el modelo.'
    END;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;
