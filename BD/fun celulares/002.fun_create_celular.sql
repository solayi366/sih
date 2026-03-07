-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 002.fun_create_celular.sql
-- DESCRIPCIÓN: Registra un celular nuevo y sus credenciales
--              (PIN/PUK) en una sola transacción atómica.
--              Valida empleado, marca y que el modelo
--              pertenezca a la marca seleccionada.
-- NOTA: p_cargo_responsable viene del formulario/importación
--       ya que tab_empleados no almacena el cargo.
-- ============================================================

CREATE OR REPLACE FUNCTION fun_create_celular(
    p_linea                 tab_celulares.linea%TYPE,
    p_imei                  tab_celulares.imei%TYPE,
    p_id_marca_cel          tab_celulares.id_marca_cel%TYPE,
    p_id_modelo_cel         tab_celulares.id_modelo_cel%TYPE,
    p_cod_nom               tab_celulares.cod_nom_responsable%TYPE,
    p_cargo_responsable     tab_celulares.cargo_responsable%TYPE,
    p_estado                tab_celulares.estado%TYPE           DEFAULT 'ASIGNADO',
    p_observaciones         tab_celulares.observaciones%TYPE    DEFAULT NULL,
    p_pin                   tab_celulares_credenciales.pin%TYPE DEFAULT NULL,
    p_puk                   tab_celulares_credenciales.puk%TYPE DEFAULT NULL
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
DECLARE
    v_nom_emple     tab_empleados.nom_emple%TYPE;
    v_id_nuevo      tab_celulares.id_celular%TYPE;
    v_imei_limpio   tab_celulares.imei%TYPE;
BEGIN
    -- ── 1. Limpiar IMEI: eliminar apóstrofe inicial si viene del Excel ────────
    v_imei_limpio := TRIM(LEADING '''' FROM TRIM(p_imei));

    -- ── 2. Validar que el empleado existe y está activo ───────────────────────
    SELECT nom_emple
    INTO   v_nom_emple
    FROM   tab_empleados
    WHERE  cod_nom = p_cod_nom
      AND  activo  = TRUE;

    IF v_nom_emple IS NULL THEN
        id_res := -1;
        msj    := 'ERROR: El código de nómina ' || p_cod_nom || ' no corresponde a un empleado activo.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- ── 3. Validar que la marca existe y está activa ──────────────────────────
    IF NOT EXISTS (
        SELECT 1 FROM tab_marcas_cel
        WHERE  id_marca_cel = p_id_marca_cel AND activo = TRUE
    ) THEN
        id_res := -2;
        msj    := 'ERROR: La marca con ID ' || p_id_marca_cel || ' no existe o está inactiva.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- ── 4. Validar que el modelo pertenece a la marca seleccionada ────────────
    -- Segunda línea de defensa: aunque el select cascada del frontend lo controle,
    -- la BD garantiza la consistencia ante cualquier manipulación del formulario.
    IF NOT EXISTS (
        SELECT 1 FROM tab_modelos_cel
        WHERE  id_modelo_cel = p_id_modelo_cel
          AND  id_marca_cel  = p_id_marca_cel
          AND  activo        = TRUE
    ) THEN
        id_res := -3;
        msj    := 'ERROR: El modelo no pertenece a la marca seleccionada o está inactivo.';
        RETURN NEXT;
        RETURN;
    END IF;

    -- ── 5. Insertar celular ───────────────────────────────────────────────────
    INSERT INTO tab_celulares (
        linea,
        imei,
        id_marca_cel,
        id_modelo_cel,
        cod_nom_responsable,
        cargo_responsable,
        estado,
        observaciones,
        activo
    ) VALUES (
        TRIM(p_linea),
        v_imei_limpio,
        p_id_marca_cel,
        p_id_modelo_cel,
        p_cod_nom,
        TRIM(COALESCE(p_cargo_responsable, '')),
        COALESCE(p_estado, 'ASIGNADO'),
        p_observaciones,
        TRUE
    )
    RETURNING id_celular INTO v_id_nuevo;

    -- ── 6. Insertar credenciales (siempre, incluso si son NULL) ──────────────
    -- Garantiza que siempre exista el registro para futuros UPDATEs.
    INSERT INTO tab_celulares_credenciales (id_celular, pin, puk)
    VALUES (
        v_id_nuevo,
        NULLIF(TRIM(COALESCE(p_pin, '')), ''),
        NULLIF(TRIM(COALESCE(p_puk, '')), '')
    );

    id_res := v_id_nuevo;
    msj    := 'SUCCESS: Celular con línea ' || TRIM(p_linea) || ' registrado correctamente.';
    RETURN NEXT;

EXCEPTION
    WHEN unique_violation THEN
        id_res := 0;
        msj    := 'ERROR: La línea o el IMEI ya existen en el sistema.';
        RETURN NEXT;
    WHEN foreign_key_violation THEN
        id_res := -4;
        msj    := 'ERROR: Referencia inválida (Marca, Modelo o Empleado no encontrado).';
        RETURN NEXT;
    WHEN invalid_text_representation THEN
        id_res := -5;
        msj    := 'ERROR: Estado inválido. Use: ASIGNADO, EN REPOSICION, EN PROCESO DE REASIGNACION o DE BAJA.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -99;
        msj    := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;
