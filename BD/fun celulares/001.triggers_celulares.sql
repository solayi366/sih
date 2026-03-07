-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 001.triggers_celulares.sql
-- DESCRIPCIÓN: Dos triggers sobre tab_celulares:
--   1. Actualiza fecha_actualizacion en cada UPDATE.
--   2. Registra automáticamente en historial cuando cambia
--      el responsable o el estado del celular.
-- NOTA: El cargo se toma de OLD/NEW.cargo_responsable porque
--       tab_empleados no almacena el cargo del empleado.
-- EJECUTAR DESPUÉS DE: 000.DDL_tablas_celulares.sql
-- ============================================================


-- ── TRIGGER 1: Timestamp automático ──────────────────────────────────────────
CREATE OR REPLACE FUNCTION fun_trg_cel_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion := CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_cel_timestamp ON tab_celulares;
CREATE TRIGGER trg_cel_timestamp
BEFORE UPDATE ON tab_celulares
FOR EACH ROW
EXECUTE FUNCTION fun_trg_cel_timestamp();


-- ── TRIGGER 2: Historial automático de cambios ───────────────────────────────
-- Solo se dispara cuando cambia el responsable o el estado.
-- Los nombres se resuelven desde tab_empleados en el momento exacto del cambio.
-- El cargo se toma directamente de OLD/NEW ya que tab_empleados no lo tiene.
-- Desnormaliza todo para que el historial sea inmutable e independiente.
CREATE OR REPLACE FUNCTION fun_trg_cel_historial()
RETURNS TRIGGER AS $$
DECLARE
    v_nom_anterior  tab_empleados.nom_emple%TYPE;
    v_nom_nuevo     tab_empleados.nom_emple%TYPE;
BEGIN
    -- Solo actúa si hubo cambio real en responsable o estado
    IF (OLD.cod_nom_responsable IS NOT DISTINCT FROM NEW.cod_nom_responsable)
       AND (OLD.estado IS NOT DISTINCT FROM NEW.estado) THEN
        RETURN NEW;
    END IF;

    -- Resolver nombres desde tab_empleados
    SELECT nom_emple INTO v_nom_anterior
    FROM   tab_empleados WHERE cod_nom = OLD.cod_nom_responsable;

    SELECT nom_emple INTO v_nom_nuevo
    FROM   tab_empleados WHERE cod_nom = NEW.cod_nom_responsable;

    INSERT INTO tab_celulares_historial (
        id_celular,
        linea_snapshot,
        cod_nom_anterior,
        nom_anterior,
        cargo_anterior,         -- Tomado de la fila OLD, no de tab_empleados
        cod_nom_nuevo,
        nom_nuevo,
        cargo_nuevo,            -- Tomado de la fila NEW, no de tab_empleados
        estado_anterior,
        estado_nuevo,
        observacion
    ) VALUES (
        NEW.id_celular,
        NEW.linea,
        OLD.cod_nom_responsable,
        COALESCE(v_nom_anterior, OLD.cod_nom_responsable),
        OLD.cargo_responsable,  -- Ya desnormalizado en la tabla principal
        NEW.cod_nom_responsable,
        COALESCE(v_nom_nuevo,   NEW.cod_nom_responsable),
        NEW.cargo_responsable,  -- Ya desnormalizado en la tabla principal
        OLD.estado,
        NEW.estado,
        NEW.observaciones
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_cel_historial ON tab_celulares;
CREATE TRIGGER trg_cel_historial
AFTER UPDATE ON tab_celulares
FOR EACH ROW
EXECUTE FUNCTION fun_trg_cel_historial();
