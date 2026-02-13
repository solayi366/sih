-- 001. Funcion crear
SELECT * FROM fun_create_usuario('administrador', 'admin123');

SELECT * FROM fun_create_usuario('administrador', 'admin1234');

--002. Funcion actualizar
SELECT * FROM fun_update_usuario(5, 'prueba', 'admin123');

--003. funcion eliminar
SELECT * FROM fun_delete_usuario(4);

-- 004. funcion leer o listar
SELECT * FROM fun_read_usuario_id(6);

select * from tab_usuarios