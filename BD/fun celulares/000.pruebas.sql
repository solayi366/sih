-- ============================================================
-- MÓDULO: Inventario de Celulares
-- ARCHIVO: 000.pruebas.sql
-- DESCRIPCIÓN: Consultas de prueba para verificar cada función.
--              Ejecutar DESPUÉS de correr todos los archivos
--              en orden (000.DDL → 001 → ... → 010).
-- NOTA: Ajustar cod_nom a valores reales de tab_empleados.
-- ============================================================


-- ════════════════════════════════════════════════════════════
-- 1. PARÁMETROS — MARCAS
-- ════════════════════════════════════════════════════════════

-- Listado de marcas con conteo de modelos activos
SELECT * FROM fun_read_marcas_cel();

-- Crear marca nueva
SELECT * FROM fun_create_marca_cel('APPLE');

-- Error esperado: ya existe
SELECT * FROM fun_create_marca_cel('SAMSUNG');

-- Actualizar nombre
SELECT * FROM fun_update_marca_cel(1, 'SAMSUNG CORP');
-- Volver al nombre original
SELECT * FROM fun_update_marca_cel(1, 'SAMSUNG');

-- Desactivar marca (solo funciona si no tiene celulares activos)
-- Desactiva también sus modelos en cascada
SELECT * FROM fun_delete_marca_cel(6);   -- HUAWEI (sin celulares en el Excel)

-- Error esperado: ya está inactiva
SELECT * FROM fun_restore_marca_cel(6);  -- Primero verificar que esté inactiva
SELECT * FROM fun_restore_marca_cel(6);  -- Segunda llamada: ERROR ya está activa

-- Error esperado: ID inexistente
SELECT * FROM fun_restore_marca_cel(999);

-- Verificar que la marca volvió a aparecer (sus modelos siguen inactivos)
SELECT * FROM fun_read_marcas_cel();

-- Error esperado: tiene celulares activos (se verá luego de insertar datos)
SELECT * FROM fun_delete_marca_cel(1);   -- SAMSUNG


-- ════════════════════════════════════════════════════════════
-- 2. PARÁMETROS — MODELOS
-- ════════════════════════════════════════════════════════════

-- Listado completo de modelos (todos, con su marca)
SELECT * FROM fun_read_modelos_cel();

-- Modelos filtrados por marca — select cascada del formulario
SELECT * FROM fun_read_modelos_por_marca(1);   -- Solo modelos SAMSUNG
SELECT * FROM fun_read_modelos_por_marca(3);   -- Solo modelos ARMOR

-- Crear modelo nuevo
SELECT * FROM fun_create_modelo_cel(1, 'A55');

-- Error esperado: ya existe para esa marca
SELECT * FROM fun_create_modelo_cel(1, 'A04');

-- Error esperado: marca inactiva
SELECT * FROM fun_create_modelo_cel(999, 'TEST');

-- Actualizar modelo
SELECT * FROM fun_update_modelo_cel(1, 1, 'A55 5G');

-- Error esperado: combinación marca+modelo ya existe
SELECT * FROM fun_update_modelo_cel(1, 1, 'A04');

-- Desactivar modelo (solo si no tiene celulares activos)
SELECT * FROM fun_delete_modelo_cel(99);   -- ID inexistente → ERROR


-- ════════════════════════════════════════════════════════════
-- 3. CREAR CELULAR
-- ════════════════════════════════════════════════════════════
-- Ajustar id_marca_cel e id_modelo_cel con los IDs reales
-- que quedaron en la BD después de correr el DDL.

-- Caso exitoso
SELECT * FROM fun_create_celular(
    '3102103496',           -- linea
    '355144112747768',      -- imei (sin apóstrofe)
    3,                      -- id_marca_cel  (ARMOR)
    12,                     -- id_modelo_cel (X5 de ARMOR)
    'S02574',               -- cod_nom (debe existir en tab_empleados)
    'CONDUCTOR MT',         -- cargo_responsable (campo libre, no viene de tab_empleados)
    'ASIGNADO',             -- estado
    NULL,                   -- observaciones
    '2906',                 -- pin
    NULL                    -- puk
);

-- Error: línea duplicada
SELECT * FROM fun_create_celular(
    '3102103496', '000000000000000', 3, 12,
    'S02574', 'CONDUCTOR MT', 'ASIGNADO', NULL, NULL, NULL
);

-- Error: empleado inexistente
SELECT * FROM fun_create_celular(
    '3100000001', '111111111111111', 1, 1,
    'NOEXISTE', 'CARGO TEST', 'ASIGNADO', NULL, NULL, NULL
);

-- Error: modelo no pertenece a la marca (marca=SAMSUNG id=1, modelo=X5 de ARMOR id=12)
SELECT * FROM fun_create_celular(
    '3100000002', '222222222222222', 1, 12,
    'S02574', 'CONDUCTOR MT', 'ASIGNADO', NULL, NULL, NULL
);

-- Error: estado inválido
SELECT * FROM fun_create_celular(
    '3100000003', '333333333333333', 3, 12,
    'S02574', 'CONDUCTOR MT', 'INEXISTENTE', NULL, NULL, NULL
);


-- ════════════════════════════════════════════════════════════
-- 4. LISTADO PAGINADO
-- ════════════════════════════════════════════════════════════

-- Sin filtros — página 1, 10 por página
SELECT * FROM fun_read_celulares(1, 10, NULL, NULL);

-- Búsqueda por marca
SELECT * FROM fun_read_celulares(1, 10, 'samsung', NULL);

-- Filtro por estado
SELECT * FROM fun_read_celulares(1, 10, NULL, 'ASIGNADO');

-- Búsqueda combinada: texto + estado
SELECT * FROM fun_read_celulares(1, 10, 'S02574', 'ASIGNADO');

-- Paginación: página 2
SELECT * FROM fun_read_celulares(2, 10, NULL, NULL);


-- ════════════════════════════════════════════════════════════
-- 5. DETALLE POR ID
-- ════════════════════════════════════════════════════════════

-- Ficha completa sin credenciales
SELECT * FROM fun_read_celular_por_id(1);

-- Credenciales (el PHP verifica es_admin = TRUE antes de llamar esto)
SELECT * FROM fun_get_credenciales_celular(1);


-- ════════════════════════════════════════════════════════════
-- 6. ACTUALIZAR — dispara trigger de historial
-- ════════════════════════════════════════════════════════════

-- Reasignar a otro responsable (trigger registra historial automáticamente)
SELECT * FROM fun_update_celular(
    1,                              -- id
    '3102103496',                   -- linea sin cambio
    '355144112747768',              -- imei sin cambio
    3,                              -- id_marca_cel
    12,                             -- id_modelo_cel
    'S03549',                       -- NUEVO cod_nom → trigger registra historial
    'MENSAJERO MOTO DE',            -- cargo del nuevo responsable
    'EN PROCESO DE REASIGNACION',   -- nuevo estado
    'Reasignado por cambio de área',
    NULL,                           -- pin: NULL = sin cambio
    NULL                            -- puk: NULL = sin cambio
);

-- Actualizar solo el PIN (cadena vacía borra el valor)
SELECT * FROM fun_update_celular(
    1, '3102103496', '355144112747768', 3, 12,
    'S03549', 'MENSAJERO MOTO DE', 'EN PROCESO DE REASIGNACION',
    NULL,
    '',     -- pin vacío = SIN BLOQUEO (borra el PIN)
    NULL
);

-- Error: modelo no pertenece a la marca
SELECT * FROM fun_update_celular(
    1, '3102103496', '355144112747768',
    1, 12,  -- marca=SAMSUNG pero modelo=X5 de ARMOR → ERROR
    'S03549', 'CARGO', 'ASIGNADO', NULL, NULL, NULL
);


-- ════════════════════════════════════════════════════════════
-- 7. HISTORIAL — generado automáticamente por el trigger
-- ════════════════════════════════════════════════════════════

-- Debe mostrar los cambios registrados en el paso anterior
SELECT * FROM fun_read_historial_celular(1);


-- ════════════════════════════════════════════════════════════
-- 8. DASHBOARD
-- ════════════════════════════════════════════════════════════

SELECT * FROM fun_get_dashboard_celulares();


-- ════════════════════════════════════════════════════════════
-- 9. SOFT DELETE
-- ════════════════════════════════════════════════════════════

SELECT * FROM fun_delete_celular(1);

-- Debe retornar vacío (está inactivo)
SELECT * FROM fun_read_celular_por_id(1);

-- Error: ya estaba dado de baja
SELECT * FROM fun_delete_celular(1);


-- ════════════════════════════════════════════════════════════
-- 10. IMPORTACIÓN MASIVA
-- ════════════════════════════════════════════════════════════
-- El modelo se envía SIN la marca ('A04', no 'SAMSUNG A04').
-- La función crea marcas y modelos nuevos automáticamente.

SELECT * FROM fun_importar_celulares('[
    {
        "linea":         "3103098198",
        "imei":          "352410488170379",
        "marca":         "SAMSUNG",
        "modelo":        "A04",
        "cod_nom":       "7658",
        "cargo":         "AUXILIAR ADMINISTRATIVO FINANCIERA",
        "pin":           null,
        "puk":           null,
        "observaciones": null
    },
    {
        "linea":         "3103099255",
        "imei":          "355144112741902",
        "marca":         "ARMOR",
        "modelo":        "X5",
        "cod_nom":       "8812",
        "cargo":         "CONDUCTOR MT",
        "pin":           "1406",
        "puk":           null,
        "observaciones": null
    },
    {
        "linea":         "3103099999",
        "imei":          "000000000000000",
        "marca":         "SAMSUNG",
        "modelo":        "A04",
        "cod_nom":       "NOEXISTE",
        "cargo":         "CARGO TEST",
        "pin":           null,
        "puk":           null,
        "observaciones": "Debe fallar — cod_nom no existe en tab_empleados"
    },
    {
        "linea":         "",
        "imei":          "111111111111111",
        "marca":         "NOKIA",
        "modelo":        "C21 PLUS",
        "cod_nom":       "S02574",
        "cargo":         "CONDUCTOR MT",
        "pin":           null,
        "puk":           null,
        "observaciones": "Debe fallar — línea vacía"
    }
]'::JSONB);
