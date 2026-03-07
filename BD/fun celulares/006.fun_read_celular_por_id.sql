-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 006.fun_read_celular_por_id.sql
-- DESCRIPCIÓN: Dos funciones de lectura individual:
--
--   1. fun_read_celular_por_id() — Ficha completa para detalle/edición.
--      Retorna IDs de marca y modelo para poblar los selects del formulario.
--
--   2. fun_get_credenciales_celular() — Solo PIN y PUK.
--      El PHP debe verificar es_admin = TRUE antes de llamarla.
-- ============================================================


-- ── FUNCIÓN 1: Detalle completo (sin credenciales) ───────────────────────────
CREATE OR REPLACE FUNCTION fun_read_celular_por_id(
    p_id tab_celulares.id_celular%TYPE
)
RETURNS TABLE (
    r_id                    INTEGER,
    r_linea                 VARCHAR,
    r_imei                  VARCHAR,
    r_id_marca_cel          INTEGER,
    r_marca                 VARCHAR,
    r_id_modelo_cel         INTEGER,
    r_modelo                VARCHAR,
    r_estado                tipo_estado_celular,
    r_responsable           VARCHAR,
    r_cod_nom               VARCHAR,
    r_cargo                 VARCHAR,
    r_area                  VARCHAR,
    r_id_area               INTEGER,
    r_observaciones         TEXT,
    r_fecha_registro        TIMESTAMP,
    r_fecha_actualizacion   TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        c.id_celular,
        c.linea,
        c.imei,
        c.id_marca_cel,
        m.nom_marca,
        c.id_modelo_cel,
        mo.nom_modelo,
        c.estado,
        e.nom_emple,
        c.cod_nom_responsable,
        c.cargo_responsable,
        ar.nom_area,
        ar.id_area,
        c.observaciones,
        c.fecha_registro,
        c.fecha_actualizacion
    FROM   tab_celulares       c
    INNER  JOIN tab_marcas_cel  m  ON c.id_marca_cel        = m.id_marca_cel
    INNER  JOIN tab_modelos_cel mo ON c.id_modelo_cel       = mo.id_modelo_cel
    LEFT   JOIN tab_empleados   e  ON c.cod_nom_responsable = e.cod_nom
    LEFT   JOIN tab_area        ar ON e.id_area             = ar.id_area
    WHERE  c.id_celular = p_id
      AND  c.activo     = TRUE;
END;
$$ LANGUAGE plpgsql;


-- ── FUNCIÓN 2: Credenciales — solo para admin ─────────────────────────────────
-- El PHP es responsable de verificar es_admin = TRUE antes de llamar esta función.
CREATE OR REPLACE FUNCTION fun_get_credenciales_celular(
    p_id tab_celulares.id_celular%TYPE
)
RETURNS TABLE (
    r_id    INTEGER,
    r_linea VARCHAR,
    r_pin   VARCHAR,
    r_puk   VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        c.id_celular,
        c.linea,
        cr.pin,
        cr.puk
    FROM   tab_celulares              c
    INNER  JOIN tab_celulares_credenciales cr ON c.id_celular = cr.id_celular
    WHERE  c.id_celular = p_id
      AND  c.activo     = TRUE;
END;
$$ LANGUAGE plpgsql;
