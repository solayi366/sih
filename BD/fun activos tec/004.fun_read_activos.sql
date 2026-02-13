CREATE OR REPLACE FUNCTION fun_read_activos(
    p_pagina INTEGER,
    p_registros_por_pagina INTEGER
)
RETURNS TABLE (
    r_id tab_activotec.id_activo%TYPE,
    r_serial tab_activotec.serial%TYPE,
    r_qr tab_activotec.codigo_qr%TYPE,
    r_hostname tab_activotec.hostname%TYPE,
    r_tipo tab_tipos.nom_tipo%TYPE,
    r_marca tab_marca.nom_marca%TYPE,
    r_modelo tab_modelo.nom_modelo%TYPE,
    r_estado tab_activotec.estado%TYPE,
    r_responsable tab_empleados.nom_emple%TYPE,
    r_area tab_area.nom_area%TYPE,
    r_padre_ref tab_activotec.referencia%TYPE,
    total_registros BIGINT
) AS $$
DECLARE
    v_offset INTEGER;
    v_total BIGINT;
BEGIN
    -- 1. Obtener el total para el control de paginación del Front
    SELECT COUNT(*) INTO v_total FROM tab_activotec WHERE activo = TRUE;

    -- 2. Calcular el desplazamiento
    v_offset := (p_pagina - 1) * p_registros_por_pagina;

    -- 3. Consulta principal (Unificada y sin SELECT *)
    RETURN QUERY 
    SELECT 
        a.id_activo, 
        a.serial, 
        a.codigo_qr, 
        a.hostname,
        t.nom_tipo, 
        m.nom_marca, 
        mo.nom_modelo, 
        a.estado, 
        e.nom_emple, 
        ar.nom_area, 
        p.referencia,
        v_total
    FROM tab_activotec a
    INNER JOIN tab_tipos t ON a.id_tipoequi = t.id_tipoequi
    INNER JOIN tab_marca m ON a.id_marca = m.id_marca
    LEFT JOIN tab_modelo mo ON a.id_modelo = mo.id_modelo
    LEFT JOIN tab_empleados e ON a.cod_nom_responsable = e.cod_nom
    LEFT JOIN tab_area ar ON e.id_area = ar.id_area
    LEFT JOIN tab_activotec p ON a.id_padre_activo = p.id_activo
    WHERE a.activo = TRUE
    ORDER BY a.id_activo DESC
    LIMIT p_registros_por_pagina
    OFFSET v_offset;
END;
$$ LANGUAGE plpgsql;