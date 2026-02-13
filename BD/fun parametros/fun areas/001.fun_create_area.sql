CREATE OR REPLACE FUNCTION fun_create_area(
    p_nombre tab_area.nom_area%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_area (nom_area, activo)
    VALUES (p_nombre, TRUE)
    RETURNING id_area, 'SUCCESS: Área registrada correctamente'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        id_res := 0;
        msj := 'ERROR: El área "' || p_nombre || '" ya existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;