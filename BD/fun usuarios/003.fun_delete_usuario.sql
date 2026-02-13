CREATE OR REPLACE FUNCTION fun_delete_usuario(
    p_id tab_usuarios.id_usuario%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_usuarios 
    SET activo = FALSE 
    WHERE id_usuario = p_id;
    
    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;
    
    IF filas_afectadas > 0 THEN
        msj := 'SUCCESS: Usuario desactivado (Borrado Lógico)';
    ELSE
        msj := 'ERROR: No se pudo desactivar el usuario';
    END IF;
    
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;