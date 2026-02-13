-- Asumiendo que Marca 'DELL' es ID 1 y Tipo 'Portátil' es ID 1
SELECT * FROM fun_create_modelo('Latitude 5420', 1, 1);
SELECT * FROM fun_create_modelo('OptiPlex 7090', 1, 1);

-- Intentar crear un modelo con una marca (ID 99) que no existe
SELECT * FROM fun_create_modelo('Modelo Fantasma', 99, 1);

-- Debe mostrar el modelo junto al nombre de su marca y tipo
SELECT * FROM fun_read_modelos();

-- Actualizar nombre
SELECT * FROM fun_update_modelo(1, 'Latitude 5420 G2', 1, 1);
-- Desactivar
SELECT * FROM fun_delete_modelo(2);