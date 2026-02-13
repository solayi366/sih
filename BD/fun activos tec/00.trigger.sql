CREATE OR REPLACE FUNCTION fun_trg_sincronizar_responsable_hijos()
RETURNS TRIGGER AS $$
BEGIN
    -- Si el responsable del padre cambia, actualizamos a todos sus hijos
    IF (OLD.cod_nom_responsable IS DISTINCT FROM NEW.cod_nom_responsable) THEN
        UPDATE tab_activotec 
        SET cod_nom_responsable = NEW.cod_nom_responsable
        WHERE id_padre_activo = NEW.id_activo;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_sincronizar_responsable ON tab_activotec;
CREATE TRIGGER trg_sincronizar_responsable
AFTER UPDATE ON tab_activotec
FOR EACH ROW
EXECUTE FUNCTION fun_trg_sincronizar_responsable_hijos();