-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 010.fun_importar_celulares.sql
-- VERSIÓN: 2.0
--
-- CAMBIOS v2:
--   1. Corregido SQLSTATE[42702]: ambiguous column 'cod_nom'
--      → Se usa prefijo de tabla en todos los WHERE: e.cod_nom
--   2. Si el empleado NO existe en tab_empleados, se crea
--      automáticamente con nombre del Excel y área derivada
--      del sufijo del cargo.
--   3. Mapa de sufijos de cargo → área:
--        MT  → MANTENIMIENTO    PT  → PATIO
--        DE  → DISTRIBUCIÓN     ME  → MENSAJERÍA
--        CES → CES              PPP → PPP
--        SAC → SAC              SST → SST
--        etc. El área se crea al vuelo si tampoco existe.
-- ============================================================

CREATE OR REPLACE FUNCTION fun_importar_celulares(
    p_registros JSONB
)
RETURNS TABLE (
    fila        INTEGER,
    linea       VARCHAR,
    cod_nom     VARCHAR,
    resultado   TEXT,
    detalle     TEXT
) AS $$
DECLARE
    v_registro        JSONB;
    v_fila            INTEGER := 0;
    v_linea           VARCHAR;
    v_imei            VARCHAR;
    v_marca_nombre    VARCHAR;
    v_modelo_nombre   VARCHAR;
    v_id_marca        INTEGER;
    v_id_modelo       INTEGER;
    v_cod_nom         VARCHAR;
    v_cargo           VARCHAR;
    v_pin             VARCHAR;
    v_puk             VARCHAR;
    v_obs             TEXT;
    v_nom_emple       tab_empleados.nom_emple%TYPE;
    v_id_area         INTEGER;
    v_area_nombre     VARCHAR;
    v_sufijo          VARCHAR;
    v_tokens          TEXT[];
    v_id_nuevo        INTEGER;
    v_empleado_creado BOOLEAN;
    v_nombre_excel    VARCHAR;
BEGIN
    FOR v_registro IN SELECT jsonb_array_elements(p_registros)
    LOOP
        v_fila            := v_fila + 1;
        v_empleado_creado := FALSE;

        v_linea         := NULLIF(TRIM(v_registro->>'linea'), '');
        v_imei          := TRIM(LEADING '''' FROM TRIM(COALESCE(v_registro->>'imei', '')));
        v_marca_nombre  := TRIM(UPPER(COALESCE(v_registro->>'marca', '')));
        v_modelo_nombre := TRIM(UPPER(COALESCE(v_registro->>'modelo', '')));
        v_cod_nom       := TRIM(v_registro->>'cod_nom');
        v_cargo         := TRIM(UPPER(COALESCE(v_registro->>'cargo', '')));
        v_pin           := NULLIF(TRIM(COALESCE(v_registro->>'pin', '')), '');
        v_puk           := NULLIF(TRIM(COALESCE(v_registro->>'puk', '')), '');
        v_obs           := NULLIF(TRIM(COALESCE(v_registro->>'observaciones', '')), '');

        -- ── Validación: Línea presente ────────────────────────────────────────
        IF v_linea IS NULL THEN
            fila := v_fila; linea := 'N/A'; cod_nom := v_cod_nom;
            resultado := 'ERROR'; detalle := 'Línea telefónica vacía. Fila omitida.';
            RETURN NEXT; CONTINUE;
        END IF;

        -- ── Validación: cod_nom presente ──────────────────────────────────────
        IF v_cod_nom IS NULL OR v_cod_nom = '' THEN
            fila := v_fila; linea := v_linea; cod_nom := '';
            resultado := 'ERROR'; detalle := 'Código de nómina vacío.';
            RETURN NEXT; CONTINUE;
        END IF;

        -- ── Buscar empleado (prefijo "e." evita ambigüedad con columna RETURNS TABLE) ──
        SELECT e.nom_emple
        INTO   v_nom_emple
        FROM   tab_empleados e
        WHERE  e.cod_nom = v_cod_nom
          AND  e.activo  = TRUE;

        -- ── Si NO existe: crear empleado automáticamente ──────────────────────
        IF v_nom_emple IS NULL THEN

            -- Derivar área desde el último token del cargo
            v_tokens  := string_to_array(v_cargo, ' ');
            v_sufijo  := v_tokens[array_length(v_tokens, 1)];

            v_area_nombre := CASE v_sufijo
                WHEN 'MT'            THEN 'MANTENIMIENTO'
                WHEN 'PT'            THEN 'PATIO'
                WHEN 'DE'            THEN 'DISTRIBUCIÓN'
                WHEN 'ME'            THEN 'MENSAJERÍA'
                WHEN 'CES'           THEN 'CES'
                WHEN 'PPP'           THEN 'PPP'
                WHEN 'SAC'           THEN 'SAC'
                WHEN 'SST'           THEN 'SST'
                WHEN 'COMERCIAL'     THEN 'COMERCIAL'
                WHEN 'COME'          THEN 'COMERCIAL'
                WHEN 'GERENCIA'      THEN 'GERENCIA'
                WHEN 'SEGURIDAD'     THEN 'SEGURIDAD'
                WHEN 'TECNOLOGIA'    THEN 'TECNOLOGÍA'
                WHEN 'FINANCIERA'    THEN 'ADMON Y FINANCIERA'
                WHEN 'MANTENIMIENTO' THEN 'MANTENIMIENTO'
                WHEN 'HUMANA'        THEN 'GESTIÓN HUMANA'
                WHEN 'SERVICIO'      THEN 'SERVICIO AL CLIENTE'
                WHEN 'SERVICIO'      THEN 'SERVICIO AL CLIENTE'
                WHEN 'DESTAJO'       THEN 'MENSAJERÍA'
                -- sufijo no reconocido: usar palabras clave del cargo completo
                ELSE CASE
                    WHEN v_cargo ILIKE '%MENSAJERO%'  THEN 'MENSAJERÍA'
                    WHEN v_cargo ILIKE '%CONDUCTOR%'  THEN 'OPERACIONES'
                    WHEN v_cargo ILIKE '%SUPERVISOR%' THEN 'OPERACIONES'
                    WHEN v_cargo ILIKE '%INSPECTOR%'  THEN 'OPERACIONES'
                    WHEN v_cargo ILIKE '%VIGILANTE%'  THEN 'SEGURIDAD'
                    ELSE 'GENERAL'
                END
            END;

            -- Buscar área (case-insensitive) o crearla
            SELECT a.id_area
            INTO   v_id_area
            FROM   tab_area a
            WHERE  UPPER(TRIM(a.nom_area)) = UPPER(TRIM(v_area_nombre))
              AND  a.activo = TRUE;

            IF v_id_area IS NULL THEN
                INSERT INTO tab_area (nom_area, activo)
                VALUES (v_area_nombre, TRUE)
                ON CONFLICT (nom_area) DO UPDATE SET activo = TRUE
                RETURNING id_area INTO v_id_area;
            END IF;

            -- Nombre del empleado: viene en el campo "responsable" del JSON
            v_nombre_excel := NULLIF(TRIM(COALESCE(v_registro->>'responsable', '')), '');
            IF v_nombre_excel IS NULL THEN
                v_nombre_excel := 'EMPLEADO ' || v_cod_nom;
            END IF;

            -- Insertar empleado evitando ON CONFLICT (causa ambigüedad con variable v_cod_nom)
            -- Estrategia: INSERT ignorando duplicado + UPDATE separado si ya existía
            INSERT INTO tab_empleados (cod_nom, nom_emple, id_area, activo)
            VALUES (v_cod_nom, v_nombre_excel, v_id_area, TRUE)
            ON CONFLICT DO NOTHING;

            -- Ahora actualizar (aplica tanto si se insertó como si ya existía)
            UPDATE tab_empleados e
               SET activo    = TRUE,
                   id_area   = v_id_area,
                   nom_emple = v_nombre_excel
             WHERE e.cod_nom = v_cod_nom
            RETURNING e.nom_emple INTO v_nom_emple;

            v_empleado_creado := TRUE;
        END IF;

        -- ── Resolución de marca ───────────────────────────────────────────────
        SELECT mc.id_marca_cel
        INTO   v_id_marca
        FROM   tab_marcas_cel mc
        WHERE  mc.nom_marca = v_marca_nombre AND mc.activo = TRUE;

        IF v_id_marca IS NULL THEN
            INSERT INTO tab_marcas_cel (nom_marca)
            VALUES (v_marca_nombre)
            ON CONFLICT (nom_marca) DO UPDATE SET activo = TRUE
            RETURNING id_marca_cel INTO v_id_marca;
        END IF;

        -- ── Resolución de modelo ──────────────────────────────────────────────
        SELECT mo.id_modelo_cel
        INTO   v_id_modelo
        FROM   tab_modelos_cel mo
        WHERE  mo.id_marca_cel = v_id_marca
          AND  mo.nom_modelo   = v_modelo_nombre
          AND  mo.activo       = TRUE;

        IF v_id_modelo IS NULL THEN
            INSERT INTO tab_modelos_cel (id_marca_cel, nom_modelo)
            VALUES (v_id_marca, v_modelo_nombre)
            ON CONFLICT (id_marca_cel, nom_modelo) DO UPDATE SET activo = TRUE
            RETURNING id_modelo_cel INTO v_id_modelo;
        END IF;

        -- ── Inserción del celular ─────────────────────────────────────────────
        BEGIN
            INSERT INTO tab_celulares (
                linea, imei, id_marca_cel, id_modelo_cel,
                cod_nom_responsable, cargo_responsable,
                estado, observaciones, activo
            ) VALUES (
                v_linea, v_imei, v_id_marca, v_id_modelo,
                v_cod_nom, COALESCE(v_cargo, ''),
                'ASIGNADO', v_obs, TRUE
            )
            RETURNING id_celular INTO v_id_nuevo;

            INSERT INTO tab_celulares_credenciales (id_celular, pin, puk)
            VALUES (v_id_nuevo, v_pin, v_puk);

            fila      := v_fila;  linea := v_linea;  cod_nom := v_cod_nom;
            resultado := 'OK';
            detalle   := 'ID ' || v_id_nuevo || ' — ' || v_nom_emple
                         || CASE WHEN v_empleado_creado
                                 THEN ' ✦ empleado creado en área: ' || v_area_nombre
                                 ELSE '' END;
            RETURN NEXT;

        EXCEPTION
            WHEN unique_violation THEN
                fila      := v_fila;  linea := v_linea;  cod_nom := v_cod_nom;
                resultado := 'ERROR';
                detalle   := 'Línea ' || v_linea || ' o IMEI ya existe en la BD. Omitido.';
                RETURN NEXT;
            WHEN OTHERS THEN
                fila      := v_fila;  linea := v_linea;  cod_nom := v_cod_nom;
                resultado := 'ERROR';
                detalle   := 'Error inesperado: ' || SQLERRM;
                RETURN NEXT;
        END;

    END LOOP;
END;
$$ LANGUAGE plpgsql;