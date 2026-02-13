SELECT * FROM fun_create_area('Sistemas e IT');
SELECT * FROM fun_create_area('Recursos Humanos');
SELECT * FROM fun_create_area('Contabilidad');

-- Debe disparar el bloque EXCEPTION
SELECT * FROM fun_create_area('Sistemas e IT');

SELECT * FROM fun_update_area(1, 'Tecnología e Información');

-- Desactivamos Contabilidad (ID 3)
SELECT * FROM fun_delete_area(3);

-- Solo deben aparecer 'Tecnología' y 'Recursos Humanos'
SELECT * FROM fun_read_areas();