CREATE OR REPLACE FUNCTION public.fun_read_activo_por_id(
    p_id_activo tab_activotec.id_activo%TYPE
)
RETURNS TABLE (
    r_id              tab_activotec.id_activo%TYPE,
    r_serial          tab_activotec.serial%TYPE,
    r_qr              tab_activotec.codigo_qr%TYPE,
    r_hostname        tab_activotec.hostname%TYPE,
    r_referencia      tab_activotec.referencia%TYPE,
    r_mac             tab_activotec.mac_activo%TYPE,
    r_ip              tab_activotec.ip_equipo%TYPE,
    r_password_activo     tab_activotec.password_activo%TYPE,
    r_estado          tab_activotec.estado%TYPE,
    r_tipo            tab_tipos.nom_tipo%TYPE,
    r_marca           tab_marca.nom_marca%TYPE,
    r_modelo          tab_modelo.nom_modelo%TYPE,
    r_id_tipo         tab_tipos.id_tipoequi%TYPE,
    r_id_marca        tab_marca.id_marca%TYPE,
    r_id_modelo       tab_modelo.id_modelo%TYPE,
    r_responsable     tab_empleados.nom_emple%TYPE,
    r_cod_responsable tab_empleados.cod_nom%TYPE,
    r_area            tab_area.nom_area%TYPE,
    r_id_area         tab_area.id_area%TYPE,
    r_id_padre        tab_activotec.id_padre_activo%TYPE,
    r_fecha_creacion  tab_activotec.fecha_creacion%TYPE
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        a.id_activo,
        a.serial,
        a.codigo_qr,
        a.hostname,
        a.referencia,
        a.mac_activo,
        a.ip_equipo,
        a.password_activo,
        a.estado,
        t.nom_tipo,
        m.nom_marca,
        mo.nom_modelo,
        a.id_tipoequi,
        a.id_marca,
        a.id_modelo,
        e.nom_emple,
        e.cod_nom,
        ar.nom_area,
        ar.id_area,
        a.id_padre_activo,
        a.fecha_creacion
    FROM tab_activotec a
    INNER JOIN tab_tipos     t   ON a.id_tipoequi        = t.id_tipoequi
    INNER JOIN tab_marca     m   ON a.id_marca           = m.id_marca
    LEFT  JOIN tab_modelo    mo  ON a.id_modelo          = mo.id_modelo
    LEFT  JOIN tab_empleados e   ON a.cod_nom_responsable = e.cod_nom
    LEFT  JOIN tab_area      ar  ON e.id_area            = ar.id_area
    WHERE a.id_activo = p_id_activo
      AND a.activo    = TRUE;
END;
$$ LANGUAGE plpgsql;