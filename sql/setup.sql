-- Veterinaria 5 de Abril
CREATE DATABASE IF NOT EXISTS vet5abril CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE vet5abril;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    orden INT NOT NULL DEFAULT 0,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio INT NOT NULL DEFAULT 0 COMMENT 'CLP',
    duracion VARCHAR(50) DEFAULT NULL COMMENT 'ej: 30 min, 1 hora',
    imagen VARCHAR(255) DEFAULT NULL,
    categoria_id INT DEFAULT NULL,
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    telefono VARCHAR(30) DEFAULT NULL,
    servicio VARCHAR(200) DEFAULT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE testimonios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    texto TEXT NOT NULL,
    estrellas TINYINT NOT NULL DEFAULT 5,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuarios: mama (admin) + propietario (editor)
-- passwords: ver archivo credenciales.txt (hashes bcrypt únicos)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Mamá (admin)',   'admin@vet5abril.cl',   '$2y$10$tpcXFojUNP0cvXUaAepfWOahhxE0hLBMVVwtOZwNnILRystEknuqy', 'admin'),
('Propietario',    'owner@vet5abril.cl',   '$2y$10$uTM0DdX3x2qxqmpEGSwVYeoB.v9QogYuEtnJVjzDCh8fMo3yP6BcO', 'editor');

-- Categorías ejemplo veterinaria
INSERT INTO categorias (nombre, orden, activa) VALUES
('Consultas',          1, 1),
('Vacunación',         2, 1),
('Cirugía',            3, 1),
('Estética y Baño',    4, 1),
('Laboratorio',        5, 1),
('Emergencias',        6, 1),
('Especialistas',      7, 1),
('Desparasitación',    8, 1);

-- Servicios ejemplo
INSERT INTO servicios (nombre, descripcion, precio, duracion, categoria_id, destacado) VALUES
('Consulta General',        'Revisión completa de salud, diagnóstico y orientación veterinaria para tu mascota.',        15000, '30 min',  1, 1),
('Consulta de Control',     'Seguimiento post-tratamiento o control periódico de salud.',                                10000, '20 min',  1, 0),
('Vacuna Rabia',            'Vacunación antirrábica obligatoria certificada por SAG.',                                    12000, '15 min',  2, 0),
('Vacuna Polivalente',      'Vacuna triple o cuádruple según edad y condición de la mascota.',                            18000, '15 min',  2, 1),
('Vacuna Leucemia Felina',  'Vacunación contra el virus de leucemia felina (FeLV).',                                      20000, '15 min',  2, 0),
('Castración Perro',        'Cirugía de esterilización para perros con control del dolor y seguimiento post-operatorio.', 85000, '1 hora',  3, 1),
('Castración Gato',         'Cirugía de esterilización felina con monitorización completa.',                               65000, '1 hora',  3, 0),
('Limpieza Dental',         'Limpieza dental profesional bajo anestesia liviana con ultrasonido.',                         45000, '45 min',  3, 0),
('Baño y Peluquería',      'Baño completo, corte de uñas, limpieza de oídos y peluquería estética.',                     15000, '45 min',  4, 1),
('Corte de Uñas',           'Corte de uñas y limpieza básica de almohadillas.',                                            5000, '10 min',  4, 0),
('Análisis de Sangre',      'Examen completo de sangre con resultados en 24 horas.',                                       25000, '15 min',  5, 0),
('Análisis de Orina',       'Examen completo de orina para diagnóstico.',                                                  15000, '10 min',  5, 0),
('Radiografía Digital',     'Radiografía digital de alta resolución.',                                                     20000, '20 min',  5, 0),
('Ecografía Abdominal',     'Ecografía completa del abdomen con imágenes digitales.',                                      30000, '30 min',  5, 0),
('Atención Urgencia 24h',   'Atención veterinaria de emergencia las 24 horas del día.',                                   35000, 'variable', 6, 1),
('Desparasitación Interna', 'Desparasitación interna según peso y tipo de mascota.',                                       8000, '10 min',  8, 0),
('Desparasitación Externa', 'Tratamiento antipulgas, garrapatas y parásitos externos.',                                   12000, '15 min',  8, 0),
('Consulta Cardiología',    'Especialista en cardiología veterinaria. ECG y ecocardiograma.',                              30000, '45 min',  7, 0),
('Consulta Dermatología',   'Especialista en dermatología. Tratamiento de alergias, hongos y parásitos cutáneos.',          25000, '40 min',  7, 0),
('Consulta Oftalmología',   'Especialista en oftalmología. Glaucoma, cataratas, conjuntivitis.',                           25000, '40 min',  7, 0);

-- Testimonios ejemplo (reemplázalos por reseñas reales desde el panel)
INSERT INTO testimonios (nombre, texto, estrellas, activo) VALUES
('María José',   'Excelente atención, mi gato se recuperó rapidísimo de su castración. Muy buena comunicación por WhatsApp.', 5, 1),
('Carlos',       'Vacunamos a nuestro perro y nos explicaron todo con mucho cariño. Precios claros, los recomiendo.',           5, 1);
