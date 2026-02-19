-- ============================================================
-- FUNCIÓN: fun_read_activo_por_id
-- Retorna el detalle completo de un activo por su ID:
--   - Datos del activo principal
--   - Tipo, marca, modelo
--   - Responsable y área
--   - Datos del padre (si es periférico)
-- ============================================================

CREATE OR REPLACE FUNCTION fun_read_activo_por_id(
    p_id_activo tab_activotec.id_activo%TYPE
)
RETURNS TABLE (
    r_id             tab_activotec.id_activo%TYPE,
    r_serial         tab_activotec.serial%TYPE,
    r_qr             tab_activotec.codigo_qr%TYPE,
    r_hostname       tab_activotec.hostname%TYPE,
    r_referencia     tab_activotec.referencia%TYPE,
    r_mac            tab_activotec.mac_activo%TYPE,
    r_ip             tab_activotec.ip_equipo%TYPE,
    r_estado         tab_activotec.estado%TYPE,
    r_tipo           tab_tipos.nom_tipo%TYPE,
    r_marca          tab_marca.nom_marca%TYPE,
    r_modelo         tab_modelo.nom_modelo%TYPE,
    r_responsable    tab_empleados.nom_emple%TYPE,
    r_cod_responsable tab_empleados.cod_nom%TYPE,
    r_area           tab_area.nom_area%TYPE,
    r_id_padre       tab_activotec.id_padre_activo%TYPE,
    r_tipo_padre     tab_tipos.nom_tipo%TYPE,
    r_qr_padre       tab_activotec.codigo_qr%TYPE
) AS $$
BEGIN
    -- Verificar que el activo exista y esté activo
    IF NOT EXISTS (
        SELECT 1 FROM tab_activotec
        WHERE id_activo = p_id_activo
          AND activo    = TRUE
    ) THEN
        RAISE EXCEPTION 'Activo con ID % no encontrado o inactivo.', p_id_activo;
    END IF;

    RETURN QUERY
    SELECT
        a.id_activo,
        a.serial,
        a.codigo_qr,
        a.hostname,
        a.referencia,
        a.mac_activo,
        a.ip_equipo,
        a.estado,
        t.nom_tipo,
        m.nom_marca,
        mo.nom_modelo,
        e.nom_emple,
        e.cod_nom,
        ar.nom_area,
        a.id_padre_activo,
        tp.nom_tipo,        -- tipo del padre
        ap.codigo_qr        -- QR del padre
    FROM tab_activotec a
    INNER JOIN tab_tipos     t   ON a.id_tipoequi        = t.id_tipoequi
    INNER JOIN tab_marca     m   ON a.id_marca            = m.id_marca
    LEFT  JOIN tab_modelo    mo  ON a.id_modelo           = mo.id_modelo
    LEFT  JOIN tab_empleados e   ON a.cod_nom_responsable = e.cod_nom
    LEFT  JOIN tab_area      ar  ON e.id_area             = ar.id_area
    LEFT  JOIN tab_activotec ap  ON a.id_padre_activo     = ap.id_activo
    LEFT  JOIN tab_tipos     tp  ON ap.id_tipoequi        = tp.id_tipoequi
    WHERE a.id_activo = p_id_activo
      AND a.activo    = TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Error en fun_read_activo_por_id: %', SQLERRM;
END;
$$ LANGUAGE plpgsql;