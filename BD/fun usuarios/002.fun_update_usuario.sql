CREATE OR REPLACE FUNCTION fun_update_usuario(
    p_id tab_usuarios.id_usuario%TYPE,
    p_username tab_usuarios.username%TYPE,
    p_contrasena tab_usuarios.contrasena%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_usuarios 
    SET username = p_username, 
        contrasena = p_contrasena
    WHERE id_usuario = p_id AND activo = TRUE;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Usuario actualizado correctamente';
    ELSE
        msj := 'ERROR: Usuario no encontrado o está inactivo';
    END IF;
    
    RETURN NEXT;

EXCEPTION WHEN OTHERS THEN
    filas_afectadas := 0;
    msj := 'ERROR CRITICO: ' || SQLERRM;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;