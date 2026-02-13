CREATE OR REPLACE FUNCTION fun_create_usuario(
    p_username tab_usuarios.username%TYPE,
    p_contrasena tab_usuarios.contrasena%TYPE
)
RETURNS TABLE (id_res INTEGER, msj TEXT) AS $$
BEGIN
    INSERT INTO tab_usuarios (username, contrasena, activo)
    VALUES (p_username, p_contrasena, TRUE)
    RETURNING id_usuario, 'SUCCESS: Usuario creado correctamente'::TEXT INTO id_res, msj;
    
    RETURN NEXT;

EXCEPTION 
    WHEN unique_violation THEN
        id_res := 0;
        msj := 'ERROR: El nombre de usuario ya se encuentra registrado';
        RETURN NEXT;
    WHEN OTHERS THEN
        id_res := -1;
        msj := 'ERROR: ' || SQLERRM;
        RETURN NEXT;
END;
$$ LANGUAGE plpgsql;