CREATE OR REPLACE FUNCTION fun_read_usuario(
    p_id tab_usuarios.id_usuario%TYPE DEFAULT NULL,
    p_username tab_usuarios.username%TYPE DEFAULT NULL
)
RETURNS TABLE (
    r_id      tab_usuarios.id_usuario%TYPE,
    r_user    tab_usuarios.username%TYPE,
    r_pass    tab_usuarios.contrasena%TYPE,
    r_activo  tab_usuarios.activo%TYPE,
    r_admin   BOOLEAN
) AS $$
BEGIN
    IF p_id IS NULL AND p_username IS NULL THEN
        RAISE EXCEPTION 'Debe proporcionar un ID o un Nombre de Usuario';
    END IF;

    RETURN QUERY
    SELECT u.id_usuario, u.username, u.contrasena, u.activo, COALESCE(u.es_admin, FALSE)
    FROM tab_usuarios u
    WHERE (p_id IS NULL OR u.id_usuario = p_id)
      AND (p_username IS NULL OR u.username = p_username);
END;
$$ LANGUAGE plpgsql;
