-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 003.fun_update_celular.sql
-- DESCRIPCIÓN: Actualiza los datos de un celular existente.
--              Valida que el modelo pertenezca a la marca.
--              El historial es registrado automáticamente
--              por el trigger trg_cel_historial.
-- NOTA: p_cargo_responsable viene del formulario ya que
--       tab_empleados no almacena el cargo del empleado.
--       p_pin / p_puk con NULL conservan el valor anterior.
--       Para borrar un PIN enviar cadena vacía ''.
-- ============================================================

CREATE OR REPLACE FUNCTION fun_update_celular(
    p_id                    tab_celulares.id_celular%TYPE,
    p_linea                 tab_celulares.linea%TYPE,
    p_imei                  tab_celulares.imei%TYPE,
    p_id_marca_cel          tab_celulares.id_marca_cel%TYPE,
    p_id_modelo_cel         tab_celulares.id_modelo_cel%TYPE,
    p_cod_nom               tab_celulares.cod_nom_responsable%TYPE,
    p_cargo_responsable     tab_celulares.cargo_responsable%TYPE,
    p_estado                tab_celulares.estado%TYPE,
    p_observaciones         tab_celulares.observaciones%TYPE    DEFAULT NULL,
    p_pin                   tab_celulares_credenciales.pin%TYPE DEFAULT NULL,
    p_puk                   tab_celulares_credenciales.puk%TYPE DEFAULT NULL
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
DECLARE
    v_nom_emple     tab_empleados.nom_emple%TYPE;
    v_imei_limpio   tab_celulares.imei%TYPE;
BEGIN
    -- ── 1. Validar que el celular existe y está activo ────────────────────────
    IF NOT EXISTS (
        SELECT 1 FROM tab_celulares
        WHERE  id_celular = p_id AND activo = TRUE
    ) THEN
        filas_afectadas := 0;
        msj := 'ERROR: No se encontró el celular con ID ' || p_id || ' o está dado de baja.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- ── 2. Limpiar IMEI ───────────────────────────────────────────────────────
    v_imei_limpio := TRIM(LEADING '''' FROM TRIM(p_imei));

    -- ── 3. Validar que el empleado existe y está activo ───────────────────────
    SELECT nom_emple
    INTO   v_nom_emple
    FROM   tab_empleados
    WHERE  cod_nom = p_cod_nom
      AND  activo  = TRUE;

    IF v_nom_emple IS NULL THEN
        filas_afectadas := 0;
        msj := 'ERROR: El código de nómina ' || p_cod_nom || ' no corresponde a un empleado activo.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- ── 4. Validar que el modelo pertenece a la marca seleccionada ────────────
    IF NOT EXISTS (
        SELECT 1 FROM tab_modelos_cel
        WHERE  id_modelo_cel = p_id_modelo_cel
          AND  id_marca_cel  = p_id_marca_cel
          AND  activo        = TRUE
    ) THEN
        filas_afectadas := 0;
        msj := 'ERROR: El modelo no pertenece a la marca seleccionada o está inactivo.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- ── 5. Actualizar celular ─────────────────────────────────────────────────
    -- COALESCE protege campos que no deben perder su valor si llegan NULL.
    -- cargo_responsable se recibe del formulario (no existe en tab_empleados).
    UPDATE tab_celulares SET
        linea               = COALESCE(TRIM(p_linea),               linea),
        imei                = COALESCE(v_imei_limpio,               imei),
        id_marca_cel        = COALESCE(p_id_marca_cel,              id_marca_cel),
        id_modelo_cel       = COALESCE(p_id_modelo_cel,             id_modelo_cel),
        cod_nom_responsable = COALESCE(p_cod_nom,                   cod_nom_responsable),
        cargo_responsable   = COALESCE(TRIM(p_cargo_responsable),   cargo_responsable),
        estado              = COALESCE(p_estado,                    estado),
        observaciones       = p_observaciones       -- NULL permitido: limpia el campo
    WHERE  id_celular = p_id
      AND  activo     = TRUE;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    -- ── 6. Actualizar credenciales ────────────────────────────────────────────
    -- NULL: no toca el campo. Cadena vacía '': borra el valor (SIN BLOQUEO).
    UPDATE tab_celulares_credenciales SET
        pin = CASE WHEN p_pin IS NOT NULL THEN NULLIF(TRIM(p_pin), '') ELSE pin END,
        puk = CASE WHEN p_puk IS NOT NULL THEN NULLIF(TRIM(p_puk), '') ELSE puk END
    WHERE  id_celular = p_id;

    msj := CASE
        WHEN filas_afectadas > 0 THEN 'SUCCESS: Celular actualizado correctamente.'
        ELSE 'ERROR: No se pudo actualizar el celular.'
    END;
    RETURN NEXT;

EXCEPTION
    WHEN unique_violation THEN
        filas_afectadas := 0;
        msj := 'ERROR: La línea o el IMEI ya pertenecen a otro celular registrado.';
        RETURN NEXT;
    WHEN invalid_text_representation THEN
        filas_afectadas := 0;
        msj := 'ERROR: Estado inválido. Use: ASIGNADO, EN REPOSICION, EN PROCESO DE REASIGNACION o DE BAJA.';
        RETURN NEXT;
    WHEN OTHERS THEN
        filas_afectadas := 0;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;
