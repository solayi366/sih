SELECT * FROM fun_create_activo(
    'SN-DELL-123',      -- Serial
    'QR-001',           -- Código QR
    'WS-IT-01',         -- Hostname
    'Laptop Latitude',   -- Referencia
    'AA:BB:CC:DD:EE:01',-- MAC
    '192.168.1.50',     -- IP
    1,                  -- ID Tipo (ej. Portátil)
    1,                  -- ID Marca (ej. Dell)
    1,                  -- ID Modelo
    'OPERATIVO',        -- Estado
    'E002',             -- Responsable (Juan Pérez)
    NULL                -- No tiene padre
);


-- Supongamos que la Laptop anterior es el ID 1
SELECT * FROM fun_create_activo(
    'SN-MOU-999',       -- Serial
    'QR-MOU-001',       -- Código QR
    NULL,               -- Hostname
    'Mouse Óptico USB',  -- Referencia
    NULL,               -- MAC
    NULL,               -- IP
    1,                  -- ID Tipo (ej. Periférico)
    1,                  -- ID Marca
    1,                  -- ID Modelo
    'OPERATIVO',        -- Estado
    NULL,               -- ¡OJO! Responsable NULL para probar herencia
    1                   -- ID Padre (La Laptop ID 1)
);

-- Actualizamos la Laptop (ID 1) para cambiar el responsable a 'E002'
-- Mantenemos los demás datos igual
SELECT * FROM fun_update_activo(
    1, 'SN-DELL-123', 'QR-001', 'WS-IT-01', 'Laptop Latitude', 
    'AA:BB:CC:DD:EE:01', '192.168.1.51', 1, 1, 1, 'OPERATIVO', 
    'E002', -- Nuevo Responsable
    NULL
);

-- AHORA VERIFICAMOS LA CASCADA
SELECT * FROM fun_read_activos_completo() 
WHERE r_qr IN ('QR-001', 'QR-MOU-001');

-- Intentar usar el mismo QR-001 en un equipo nuevo
SELECT * FROM fun_create_activo(
    'SN-NUEVO', 'QR-001', 'WS-TEST', 'Test', NULL, NULL, 1, 1, 1, 'OP', 'E001', NULL
);