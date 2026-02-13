CREATE OR REPLACE FUNCTION fun_create_modelo(
    p_nombre tab_modelo.nom_modelo%TYPE,
    p_id_marca tab_modelo.id_marca%TYPE,
    p_id_tipo tab_modelo.id_tipoequi%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_modelo (nom_modelo, id_marca, id_tipoequi, activo)
    VALUES (p_nombre, p_id_marca, p_id_tipo, TRUE)
    RETURNING id_modelo, 'SUCCESS: Modelo registrado correctamente'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN foreign_key_violation THEN
        id_res := 0;
        msj := 'ERROR: La Marca o el Tipo de Dispositivo especificado no existe.';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj := 'ERROR CRÍTICO: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;