CREATE OR REPLACE FUNCTION fun_update_activo(
    p_id        INTEGER,
    p_serial    VARCHAR,
    p_qr        VARCHAR,
    p_hostname  VARCHAR,
    p_ref       VARCHAR,
    p_mac       VARCHAR,
    p_ip        VARCHAR,
    p_tipo      INTEGER,
    p_marca     INTEGER,
    p_modelo    INTEGER,
    p_estado    VARCHAR,
    p_resp      VARCHAR,
    p_padre     INTEGER,
    p_password  TEXT DEFAULT NULL
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_activotec SET
        serial              = COALESCE(p_serial,   serial),
        codigo_qr           = COALESCE(p_qr,       codigo_qr),
        hostname            = p_hostname,
        referencia          = p_ref,
        mac_activo          = p_mac,
        ip_equipo           = p_ip,
        id_tipoequi         = COALESCE(p_tipo,     id_tipoequi),
        id_marca            = COALESCE(p_marca,    id_marca),
        id_modelo           = COALESCE(p_modelo, id_modelo),
        estado              = COALESCE(p_estado,   estado),
        cod_nom_responsable = COALESCE(p_resp,     cod_nom_responsable),
        id_padre_activo     = p_padre,
        password_activo     = CASE WHEN p_password IS NOT NULL THEN p_password ELSE password_activo END
    WHERE id_activo = p_id AND activo = TRUE;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    msj := CASE WHEN filas_afectadas > 0 THEN 'SUCCESS: Activo actualizado' ELSE 'ERROR: No se encontró el activo' END;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;