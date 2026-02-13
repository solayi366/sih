-- 1. Tablas Maestras (Sin llaves foráneas dependientes)
CREATE TABLE tab_area (
    id_area SERIAL PRIMARY KEY, 
    nom_area VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE tab_marca (
    id_marca SERIAL PRIMARY KEY, 
    nom_marca VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE tab_tipos (
    id_tipoequi SERIAL PRIMARY KEY, 
    nom_tipo VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE tab_usuarios (
    id_usuario SERIAL PRIMARY KEY, 
    username VARCHAR(50) NOT NULL UNIQUE, 
    contrasena VARCHAR(255) NOT NULL
);

-- 2. Tablas con dependencias simples
CREATE TABLE tab_empleados (
    cod_nom VARCHAR(6) PRIMARY KEY, 
    nom_emple VARCHAR(100) NOT NULL, 
    id_area INTEGER NOT NULL, 
    activo BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_area FOREIGN KEY(id_area) REFERENCES tab_area (id_area)
);

CREATE TABLE tab_modelo (
    id_modelo SERIAL PRIMARY KEY, 
    nom_modelo VARCHAR(100) NOT NULL, 
    id_marca INTEGER NOT NULL, 
    id_tipoequi INTEGER, 
    CONSTRAINT fk_marca FOREIGN KEY(id_marca) REFERENCES tab_marca (id_marca),
    CONSTRAINT fk_tipo FOREIGN KEY(id_tipoequi) REFERENCES tab_tipos (id_tipoequi)
);

-- 3. Tabla principal de Activos (con autorreferencia)
CREATE TABLE tab_activotec (
    id_activo SERIAL PRIMARY KEY, 
    serial VARCHAR(100) UNIQUE, 
    codigo_qr VARCHAR(50) UNIQUE, 
    hostname VARCHAR(100), 
    referencia VARCHAR(100), 
    mac_activo VARCHAR(17), 
    ip_equipo VARCHAR(15), 
    id_tipoequi INTEGER NOT NULL, 
    id_marca INTEGER NOT NULL, 
    id_modelo INTEGER, 
    estado VARCHAR(20) NOT NULL, 
    cod_nom_responsable VARCHAR(6), 
    id_padre_activo INTEGER, 
    CONSTRAINT fk_tipo_act FOREIGN KEY(id_tipoequi) REFERENCES tab_tipos (id_tipoequi),
    CONSTRAINT fk_marca_act FOREIGN KEY(id_marca) REFERENCES tab_marca (id_marca),
    CONSTRAINT fk_modelo_act FOREIGN KEY(id_modelo) REFERENCES tab_modelo (id_modelo),
    CONSTRAINT fk_responsable FOREIGN KEY(cod_nom_responsable) REFERENCES tab_empleados (cod_nom),
    CONSTRAINT fk_padre FOREIGN KEY(id_padre_activo) REFERENCES tab_activotec (id_activo)
);

-- 4. Tablas de historial y reportes
CREATE TABLE tab_actualizaciones (
    id_evento SERIAL PRIMARY KEY, 
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    id_activo INTEGER NOT NULL, 
    tipo_evento VARCHAR(50) NOT NULL, 
    desc_evento TEXT NOT NULL, 
    usuario_sistema VARCHAR(50), 
    CONSTRAINT fk_activo_act FOREIGN KEY(id_activo) REFERENCES tab_activotec (id_activo)
);

CREATE TABLE tab_novedades (
    id_novedad SERIAL PRIMARY KEY, 
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    cedula_reportante VARCHAR(50), 
    nombre_reportante VARCHAR(100), 
    id_activo INTEGER, 
    tipo_dano VARCHAR(50), 
    descripcion VARCHAR(500), 
    evidencia_foto VARCHAR(255), 
    estado_ticket VARCHAR(20), 
    CONSTRAINT fk_activo_nov FOREIGN KEY(id_activo) REFERENCES tab_activotec (id_activo)
);