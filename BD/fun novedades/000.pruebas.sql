-- El usuario solo sabe su código 'E001'
SELECT * FROM fun_create_novedad(
    'E002', 
    'TECLADO', 
    'La tecla Enter no funciona después de un derrame de café.', 
    'fotos/novedad_teclado.png'
);

SELECT * FROM fun_read_novedades(1, 10);