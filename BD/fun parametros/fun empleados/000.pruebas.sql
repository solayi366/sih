-- Suponiendo que 'Sistemas' tiene el ID 1
SELECT * FROM fun_create_empleado('E001', 'Juan Pérez', 1);
SELECT * FROM fun_create_empleado('E002', 'Ana María Silva', 1);

-- Intentar asignar un área (ID 999) que no existe
SELECT * FROM fun_create_empleado('E003', 'Fallo de Área', 999);

-- Intentar usar el código 'E001' otra vez
SELECT * FROM fun_create_empleado('E001', 'Persona Duplicada', 1);

SELECT * FROM fun_read_empleados();

-- Actualizar nombre de Ana
SELECT * FROM fun_update_empleado('E002', 'Ana María Silva López', 1);
-- Desactivar a Juan
SELECT * FROM fun_delete_empleado('E001');