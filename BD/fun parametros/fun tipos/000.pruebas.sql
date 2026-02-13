SELECT * FROM fun_create_tipo('Portátil');
SELECT * FROM fun_create_tipo('Impresora');

-- Intenta insertar 'Portátil' de nuevo
SELECT * FROM fun_create_tipo('Portátil');

-- Cambia el nombre del ID 1
SELECT * FROM fun_update_tipo(1, 'Laptop / Portátil');

-- Desactiva el 2
SELECT * FROM fun_delete_tipo(2);
-- Lista activos (solo debería salir el 1)
SELECT * FROM fun_read_tipos();