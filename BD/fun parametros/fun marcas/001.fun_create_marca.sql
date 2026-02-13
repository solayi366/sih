CREATE OR REPLACE FUNCTION fun_create_marca(
    p_nombre tab_marca.nom_marca%TYPE,
    p_id_tipo tab_marca.id_tipoequi%TYPE  -- Nuevo parámetro
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_marca (nom_marca, id_tipoequi, activo)
    VALUES (p_nombre, p_id_tipo, TRUE)
    RETURNING id_marca, ('SUCCESS: Marca "' || p_nombre || '" registrada correctamente')::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        id_res := 0;
        msj := 'ERROR: La marca "' || p_nombre || '" ya existe para este tipo de equipo.';
        RETURN NEXT;
    WHEN foreign_key_violation THEN
        id_res := -1;
        msj := 'ERROR: El tipo de equipo especificado no existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -2;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;