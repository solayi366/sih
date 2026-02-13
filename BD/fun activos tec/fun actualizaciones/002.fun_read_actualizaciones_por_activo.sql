CREATE OR REPLACE FUNCTION fun_read_actualizaciones_por_activo(
    p_id_activo tab_actualizaciones.id_activo%TYPE,
    p_pagina INTEGER,
    p_registros_por_pagina INTEGER
)
RETURNS TABLE (
    r_fecha tab_actualizaciones.fecha%TYPE,
    r_tipo tab_actualizaciones.tipo_evento%TYPE,
    r_descripcion tab_actualizaciones.desc_evento%TYPE,
    r_usuario tab_actualizaciones.usuario_sistema%TYPE,
    total_registros BIGINT
) AS $$
DECLARE
    v_offset INTEGER;
    v_total BIGINT;
BEGIN
    -- 1. Contamos el total de eventos para este activo
    SELECT COUNT(*) INTO v_total 
    FROM tab_actualizaciones 
    WHERE id_activo = p_id_activo;

    -- 2. Calculamos el desplazamiento
    v_offset := (p_pagina - 1) * p_registros_por_pagina;

    -- 3. Consulta detallada
    RETURN QUERY 
    SELECT 
        fecha, 
        tipo_evento, 
        desc_evento, 
        usuario_sistema, 
        v_total
    FROM tab_actualizaciones
    WHERE id_activo = p_id_activo
    ORDER BY fecha DESC
    LIMIT p_registros_por_pagina
    OFFSET v_offset;
END;
$$ LANGUAGE plpgsql;