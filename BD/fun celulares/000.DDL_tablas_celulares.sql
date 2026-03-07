-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 000.DDL_tablas_celulares.sql [v2 — con tab_modelos_cel]
-- DESCRIPCIÓN: Definición de tablas, tipos, constraints e índices
--              del módulo de celulares. Completamente independiente
--              del módulo de activos TEC, pero integrado al mismo
--              esquema de empleados y usuarios del SIH.
-- EJECUTAR: psql -U postgres -d db_sih -f 000.DDL_tablas_celulares.sql
-- ============================================================


-- ── 1. TIPO ENUMERADO: Estados del ciclo de vida ──────────────────────────────
-- Centraliza los estados válidos. Cualquier valor fuera de este enum
-- es rechazado a nivel de BD antes de llegar a la función.
DO $$ BEGIN
    CREATE TYPE tipo_estado_celular AS ENUM (
        'ASIGNADO',
        'EN REPOSICION',
        'EN PROCESO DE REASIGNACION',
        'DE BAJA'
    );
EXCEPTION
    WHEN duplicate_object THEN
        RAISE NOTICE 'El tipo tipo_estado_celular ya existe, se omite.';
END $$;


-- ── 2. CATÁLOGO DE MARCAS ────────────────────────────────────────────────────
-- Separado intencionalmente de tab_marca (activos TEC) para no acoplar módulos.
-- Gestionable desde la pantalla de parámetros del admin.
CREATE TABLE IF NOT EXISTS tab_marcas_cel (
    id_marca_cel    SERIAL          PRIMARY KEY,
    nom_marca       VARCHAR(80)     NOT NULL,
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    fecha_creacion  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_marcas_cel_nombre UNIQUE (nom_marca)
);

COMMENT ON TABLE  tab_marcas_cel           IS 'Catálogo de marcas de celulares. Gestionable desde parámetros del admin.';
COMMENT ON COLUMN tab_marcas_cel.nom_marca IS 'Nombre comercial de la marca (ej: SAMSUNG, NOKIA, ARMOR).';
COMMENT ON COLUMN tab_marcas_cel.activo    IS 'Soft-delete: FALSE oculta la marca sin eliminar datos históricos.';


-- ── 3. CATÁLOGO DE MODELOS ───────────────────────────────────────────────────
-- Ligado a la marca: al seleccionar SAMSUNG solo aparecen sus modelos.
-- Mismo patrón que tab_modelo → tab_marca en el módulo TEC.
-- Gestionable desde la pantalla de parámetros del admin.
CREATE TABLE IF NOT EXISTS tab_modelos_cel (
    id_modelo_cel   SERIAL          PRIMARY KEY,
    id_marca_cel    INTEGER         NOT NULL,
    nom_modelo      VARCHAR(100)    NOT NULL,   -- Solo el modelo: 'A04', 'A15', 'C21 Plus', 'X5'
    activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    fecha_creacion  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Mismo modelo puede repetirse en marcas distintas, no dentro de la misma
    CONSTRAINT uq_modelos_cel_marca_modelo  UNIQUE (id_marca_cel, nom_modelo),
    CONSTRAINT fk_modelos_cel_marca         FOREIGN KEY (id_marca_cel)
                                            REFERENCES tab_marcas_cel(id_marca_cel)
                                            ON UPDATE CASCADE
);

COMMENT ON TABLE  tab_modelos_cel              IS 'Catálogo de modelos filtrado por marca. Gestionable desde parámetros del admin.';
COMMENT ON COLUMN tab_modelos_cel.id_marca_cel IS 'Marca propietaria del modelo. Define el filtro cascada en el formulario de registro.';
COMMENT ON COLUMN tab_modelos_cel.nom_modelo   IS 'Solo el modelo sin la marca (ej: A04, A15, C21 Plus). La marca se une al consultar.';
COMMENT ON COLUMN tab_modelos_cel.activo       IS 'Soft-delete: FALSE oculta el modelo sin afectar celulares ya registrados con él.';


-- ── 4. TABLA PRINCIPAL DE CELULARES ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tab_celulares (
    id_celular          SERIAL                  PRIMARY KEY,
    linea               VARCHAR(15)             NOT NULL,   -- Número de línea: identificador de negocio único
    imei                VARCHAR(20)             NOT NULL,   -- IMEI del equipo físico
    id_marca_cel        INTEGER                 NOT NULL,
    id_modelo_cel       INTEGER                 NOT NULL,
    cod_nom_responsable VARCHAR(20)             NOT NULL,   -- FK a tab_empleados.cod_nom
    cargo_responsable   VARCHAR(120)            NOT NULL,   -- Desnormalizado para etiqueta y listados sin JOIN extra
    estado              tipo_estado_celular     NOT NULL DEFAULT 'ASIGNADO',
    observaciones       TEXT,
    activo              BOOLEAN                 NOT NULL DEFAULT TRUE,
    fecha_registro      TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_celulares_linea   UNIQUE (linea),
    CONSTRAINT uq_celulares_imei    UNIQUE (imei),
    CONSTRAINT fk_celulares_marca   FOREIGN KEY (id_marca_cel)
                                    REFERENCES tab_marcas_cel(id_marca_cel)
                                    ON UPDATE CASCADE,
    CONSTRAINT fk_celulares_modelo  FOREIGN KEY (id_modelo_cel)
                                    REFERENCES tab_modelos_cel(id_modelo_cel)
                                    ON UPDATE CASCADE,
    CONSTRAINT fk_celulares_emple   FOREIGN KEY (cod_nom_responsable)
                                    REFERENCES tab_empleados(cod_nom)
                                    ON UPDATE CASCADE
);

COMMENT ON TABLE  tab_celulares                      IS 'Inventario principal de celulares corporativos.';
COMMENT ON COLUMN tab_celulares.linea                IS 'Número de línea telefónica: identificador único de negocio.';
COMMENT ON COLUMN tab_celulares.imei                 IS 'IMEI del equipo. Los que vienen con apóstrofe del Excel se almacenan limpios.';
COMMENT ON COLUMN tab_celulares.id_modelo_cel        IS 'FK a tab_modelos_cel. El modelo debe pertenecer a la misma marca del celular.';
COMMENT ON COLUMN tab_celulares.cargo_responsable    IS 'Cargo desnormalizado del responsable al momento del registro o última actualización.';
COMMENT ON COLUMN tab_celulares.estado               IS 'ASIGNADO | EN REPOSICION | EN PROCESO DE REASIGNACION | DE BAJA.';
COMMENT ON COLUMN tab_celulares.activo               IS 'Soft-delete: FALSE elimina lógicamente el registro.';
COMMENT ON COLUMN tab_celulares.fecha_actualizacion  IS 'Actualizado automáticamente por trigger en cada UPDATE.';


-- ── 5. TABLA DE CREDENCIALES (solo admin) ────────────────────────────────────
-- Separada de tab_celulares por seguridad. El PHP solo la consulta
-- si el usuario tiene es_admin = TRUE en tab_usuarios.
CREATE TABLE IF NOT EXISTS tab_celulares_credenciales (
    id_celular  INTEGER     PRIMARY KEY,
    pin         VARCHAR(20),
    puk         VARCHAR(30),

    CONSTRAINT fk_cred_celular FOREIGN KEY (id_celular)
                               REFERENCES tab_celulares(id_celular)
                               ON DELETE CASCADE
);

COMMENT ON TABLE  tab_celulares_credenciales     IS 'Credenciales sensibles (PIN/PUK). Solo accesible si es_admin = TRUE.';
COMMENT ON COLUMN tab_celulares_credenciales.pin IS 'PIN de desbloqueo. NULL = SIN BLOQUEO.';
COMMENT ON COLUMN tab_celulares_credenciales.puk IS 'Código PUK de la SIM. NULL si no se dispone.';


-- ── 6. TABLA DE HISTORIAL DE REASIGNACIONES ──────────────────────────────────
-- Escrita automáticamente por trigger. Nunca se modifica manualmente.
-- Los nombres se desnormalizan en el momento exacto del cambio para
-- que el historial sea inmutable e independiente de tab_empleados.
CREATE TABLE IF NOT EXISTS tab_celulares_historial (
    id_historial        SERIAL                  PRIMARY KEY,
    id_celular          INTEGER                 NOT NULL,
    linea_snapshot      VARCHAR(15)             NOT NULL,
    cod_nom_anterior    VARCHAR(20),
    nom_anterior        VARCHAR(120),
    cargo_anterior      VARCHAR(120),
    cod_nom_nuevo       VARCHAR(20),
    nom_nuevo           VARCHAR(120),
    cargo_nuevo         VARCHAR(120),
    estado_anterior     tipo_estado_celular,
    estado_nuevo        tipo_estado_celular,
    observacion         TEXT,
    fecha_cambio        TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_hist_celular FOREIGN KEY (id_celular)
                               REFERENCES tab_celulares(id_celular)
                               ON DELETE CASCADE
);

COMMENT ON TABLE  tab_celulares_historial                IS 'Auditoría completa e inmutable de cambios de responsable y estado.';
COMMENT ON COLUMN tab_celulares_historial.linea_snapshot IS 'Snapshot de la línea al momento del cambio.';
COMMENT ON COLUMN tab_celulares_historial.nom_anterior   IS 'Nombre del responsable anterior desnormalizado: no cambia si el empleado es editado.';
COMMENT ON COLUMN tab_celulares_historial.nom_nuevo      IS 'Nombre del nuevo responsable desnormalizado al momento del cambio.';


-- ── 7. ÍNDICES DE RENDIMIENTO ─────────────────────────────────────────────────

-- Listado principal filtra por estado
CREATE INDEX IF NOT EXISTS idx_celulares_estado
    ON tab_celulares (estado)
    WHERE activo = TRUE;

-- Búsqueda por responsable
CREATE INDEX IF NOT EXISTS idx_celulares_responsable
    ON tab_celulares (cod_nom_responsable)
    WHERE activo = TRUE;

-- Filtro cascada: al seleccionar marca se carga la lista de sus modelos
CREATE INDEX IF NOT EXISTS idx_modelos_cel_marca
    ON tab_modelos_cel (id_marca_cel)
    WHERE activo = TRUE;

-- Historial siempre se consulta por celular
CREATE INDEX IF NOT EXISTS idx_historial_celular
    ON tab_celulares_historial (id_celular, fecha_cambio DESC);


-- ── 8. DATOS INICIALES: Marcas y Modelos base ────────────────────────────────
-- Extraídos del Excel de origen. ON CONFLICT evita duplicados en re-ejecución.
-- Los modelos se guardan SIN el nombre de la marca (solo 'A04', no 'SAMSUNG A04').

INSERT INTO tab_marcas_cel (nom_marca) VALUES
    ('SAMSUNG'),
    ('NOKIA'),
    ('ARMOR'),
    ('MOTOROLA'),
    ('XIAOMI'),
    ('HUAWEI')
ON CONFLICT (nom_marca) DO NOTHING;

INSERT INTO tab_modelos_cel (id_marca_cel, nom_modelo)
SELECT m.id_marca_cel, mod.nom_modelo
FROM (VALUES
    ('SAMSUNG', 'A03'),
    ('SAMSUNG', 'A04'),
    ('SAMSUNG', 'A05'),
    ('SAMSUNG', 'A05S'),
    ('SAMSUNG', 'A06'),
    ('SAMSUNG', 'A07'),
    ('SAMSUNG', 'A15'),
    ('SAMSUNG', 'A22S'),
    ('SAMSUNG', 'A23'),
    ('SAMSUNG', 'S21 FE'),
    ('NOKIA',   'C21 PLUS'),
    ('ARMOR',   'X5')
) AS mod(nom_marca, nom_modelo)
INNER JOIN tab_marcas_cel m ON m.nom_marca = mod.nom_marca
ON CONFLICT (id_marca_cel, nom_modelo) DO NOTHING;
