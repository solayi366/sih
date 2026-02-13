SELECT * FROM fun_create_marca('DELL');
SELECT * FROM fun_create_marca('HP');
SELECT * FROM fun_create_marca('LENOVO');


-- Debe devolver el mensaje de error personalizado
SELECT * FROM fun_create_marca('HP');


-- Cambiamos el nombre de la primera marca
SELECT * FROM fun_update_marca(1, 'DELL TECHNOLOGIES');

-- Desactivamos LENOVO (ID 3)
SELECT * FROM fun_delete_marca(3);


-- Solo deben aparecer DELL TECHNOLOGIES y HP
SELECT * FROM fun_read_marcas();