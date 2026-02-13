CREATE OR REPLACE FUNCTION fun_create_marca(
    p_nombre tab_marca.nom_marca%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_marca (nom_marca, activo)
    VALUES (p_nombre, TRUE)
    RETURNING id_marca, 'SUCCESS: Marca registrada correctamente'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        id_res := 0;
        msj := 'ERROR: La marca "' || p_nombre || '" ya existe en el sistema.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;