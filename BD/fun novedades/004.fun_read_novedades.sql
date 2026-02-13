CREATE OR REPLACE FUNCTION fun_read_novedades(
    p_pagina INTEGER,
    p_registros_por_pagina INTEGER
)
RETURNS TABLE (
    r_id INTEGER,
    r_fecha TIMESTAMP,
    r_cod_nom VARCHAR,
    r_nombre_reportante VARCHAR,
    r_activo_qr VARCHAR,
    r_activo_ref VARCHAR,
    r_tipo_dano VARCHAR,
    r_descripcion VARCHAR,
    r_foto VARCHAR,
    r_estado VARCHAR,
    total_registros BIGINT
) AS $$
DECLARE
    v_offset INTEGER;
    v_total BIGINT;
BEGIN
    SELECT COUNT(*) INTO v_total FROM tab_novedades WHERE activo = TRUE;
    v_offset := (p_pagina - 1) * p_registros_por_pagina;

    RETURN QUERY 
    SELECT 
        n.id_novedad, 
        n.fecha_reporte, 
        n.cedula_reportante, -- Aquí va el código de nómina
        n.nombre_reportante, 
        a.codigo_qr, 
        a.referencia,
        n.tipo_dano, 
        n.descripcion, 
        n.evidencia_foto, 
        n.estado_ticket,
        v_total
    FROM tab_novedades n
    LEFT JOIN tab_activotec a ON n.id_activo = a.id_activo
    WHERE n.activo = TRUE
    ORDER BY n.fecha_reporte DESC
    LIMIT p_registros_por_pagina
    OFFSET v_offset;
END;
$$ LANGUAGE plpgsql;