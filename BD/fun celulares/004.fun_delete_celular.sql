-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 004.fun_delete_celular.sql
-- DESCRIPCIÓN: Soft-delete de un celular (activo = FALSE).
--              Igual que el patrón del módulo TEC: nunca se
--              elimina físicamente el registro de la BD.
--              El historial permanece intacto.
-- ============================================================

CREATE OR REPLACE FUNCTION fun_delete_celular(
    p_id tab_celulares.id_celular%TYPE
)
RETURNS TABLE (filas_afectadas INTEGER, msj TEXT) AS $$
BEGIN
    UPDATE tab_celulares
    SET    activo = FALSE
    WHERE  id_celular = p_id
      AND  activo     = TRUE;

    GET DIAGNOSTICS filas_afectadas = ROW_COUNT;

    msj := CASE
        WHEN filas_afectadas > 0
            THEN 'SUCCESS: Celular desactivado correctamente.'
        ELSE
            'ERROR: No se encontró el celular o ya estaba dado de baja.'
    END;
    RETURN NEXT;
END;
$$ LANGUAGE plpgsql;
