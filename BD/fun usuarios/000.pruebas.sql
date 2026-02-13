-- 001. Funcion crear
SELECT * FROM fun_create_usuario('admin', 'admin123');

SELECT * FROM fun_create_usuario('administrador', 'admin1234');

--002. Funcion actualizar
SELECT * FROM fun_update_usuario(1, 'prueba', 'admin123');

--003. funcion eliminar
SELECT * FROM fun_delete_usuario(3);

-- 004. funcion leer o listar
SELECT * FROM fun_read_usuario(1);

select * from tab_usuarios