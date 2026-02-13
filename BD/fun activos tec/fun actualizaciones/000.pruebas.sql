-- Asumiendo que el Activo ID 1 es la Laptop Dell que creamos antes
SELECT * FROM fun_create_actualizacion(
    1, 
    'MANTENIMIENTO PREVENTIVO', 
    'Limpieza de ventiladores y cambio de pasta térmica.', 
    'admin_sih'
);

SELECT * FROM fun_create_actualizacion(
    1, 
    'CAMBIO DE ESTADO', 
    'Se cambia estado de OPERATIVO a REPARACION por fallo en batería.', 
    'tecnico_01'
);


-- Ver los eventos del Activo 1, de 5 en 5
SELECT * FROM fun_read_actualizaciones_por_activo(1, 1, 5);