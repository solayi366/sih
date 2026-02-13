CREATE OR REPLACE FUNCTION fun_create_tipo(
    p_nombre tab_tipos.nom_tipo%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_tipos (nom_tipo, activo)
    VALUES (p_nombre, TRUE)
    RETURNING id_tipoequi, 'SUCCESS: Tipo de dispositivo registrado'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        id_res := 0;
        msj := 'ERROR: El tipo de dispositivo "' || p_nombre || '" ya existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;