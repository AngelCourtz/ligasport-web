-- ==========================================================
-- LIGASPORT - Sistema web de gestión de ventas e inventario
-- Base de datos del prototipo (modelo de la Figura 8 de la tesis)
-- Versión para hosting: importar dentro de la base ya creada en el panel.
-- ==========================================================

CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL COMMENT 'hash bcrypt (password_hash)',
  rol ENUM('administrador','vendedor') NOT NULL DEFAULT 'vendedor'
) ENGINE=InnoDB;

CREATE TABLE cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  telefono VARCHAR(20) NOT NULL DEFAULT '',
  direccion VARCHAR(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB;

CREATE TABLE producto (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  marca VARCHAR(50) NOT NULL,
  modelo VARCHAR(100) NOT NULL,
  talla VARCHAR(5) NOT NULL,
  color VARCHAR(50) NOT NULL,
  precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
  stock_actual INT NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
  UNIQUE KEY uq_producto (marca, modelo, color, talla),
  KEY idx_busqueda (modelo, talla)
) ENGINE=InnoDB;

CREATE TABLE movimiento_inventario (
  id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  id_usuario INT NOT NULL,
  tipo ENUM('entrada','salida') NOT NULL,
  cantidad INT NOT NULL CHECK (cantidad > 0),
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mov_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
  CONSTRAINT fk_mov_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE venta (
  id_venta INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_usuario INT NOT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  canal ENUM('Local','WhatsApp','Facebook','TikTok') NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_venta_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_venta_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
  KEY idx_fecha (fecha)
) ENGINE=InnoDB;

CREATE TABLE detalle_venta (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL CHECK (cantidad > 0),
  precio_unitario DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_det_venta FOREIGN KEY (id_venta) REFERENCES venta(id_venta) ON DELETE CASCADE,
  CONSTRAINT fk_det_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB;

-- ---------------- Datos de prueba ----------------
-- Usuarios de demostración: ver README.md
INSERT INTO usuario (nombre, usuario, contrasena, rol) VALUES
  ('Administrador LIGASPORT', 'admin', '$2y$10$aixMEWvL9paQdjwmmULS7.uUTgWKY8I82d6z8BkIrPF8mLJ0wAy.6', 'administrador'),
  ('Vendedor de turno', 'vendedor', '$2y$10$Oa2Mr56.Wz7N8CZZm/5gh.huV6dgQtFmUHeSDNwSc0LZc//DVva9y', 'vendedor');

INSERT INTO cliente (nombre, telefono, direccion) VALUES
  ('Cliente de mostrador', '', 'Venta en el local'),
  ('Lucía Ramos', '987 654 301', 'Comas, Lima'),
  ('Jorge Quispe', '987 654 302', 'Los Olivos, Lima'),
  ('María Fernández', '987 654 303', 'San Martín de Porres, Lima'),
  ('Diego Huamán', '987 654 304', 'Carabayllo, Lima'),
  ('Valeria Torres', '987 654 305', 'Independencia, Lima'),
  ('Kevin Rojas', '987 654 306', 'Arequipa (envío por Shalom)'),
  ('Andrea Salazar', '987 654 307', 'Trujillo (envío por Shalom)'),
  ('Luis Mendoza', '987 654 308', 'Puente Piedra, Lima');

INSERT INTO producto (id_producto, marca, modelo, talla, color, precio, stock_actual) VALUES
  (1, 'Nike', 'Air Force 1 ''07', '37', 'Blanco', 429.00, 3),
  (2, 'Nike', 'Air Force 1 ''07', '38', 'Blanco', 429.00, 5),
  (3, 'Nike', 'Air Force 1 ''07', '39', 'Blanco', 429.00, 6),
  (4, 'Nike', 'Air Force 1 ''07', '40', 'Blanco', 429.00, 6),
  (5, 'Nike', 'Air Force 1 ''07', '41', 'Blanco', 429.00, 8),
  (6, 'Nike', 'Air Force 1 ''07', '42', 'Blanco', 429.00, 9),
  (7, 'Nike', 'Air Force 1 ''07', '43', 'Blanco', 429.00, 0),
  (8, 'Nike', 'Dunk Low Retro', '38', 'Blanco/Negro', 459.00, 4),
  (9, 'Nike', 'Dunk Low Retro', '39', 'Blanco/Negro', 459.00, 3),
  (10, 'Nike', 'Dunk Low Retro', '40', 'Blanco/Negro', 459.00, 4),
  (11, 'Nike', 'Dunk Low Retro', '41', 'Blanco/Negro', 459.00, 4),
  (12, 'Nike', 'Dunk Low Retro', '42', 'Blanco/Negro', 459.00, 3),
  (13, 'Nike', 'Dunk Low Retro', '43', 'Blanco/Negro', 459.00, 8),
  (14, 'Jordan', 'Air Jordan 1 Mid', '38', 'Negro/Rojo', 549.00, 5),
  (15, 'Jordan', 'Air Jordan 1 Mid', '39', 'Negro/Rojo', 549.00, 7),
  (16, 'Jordan', 'Air Jordan 1 Mid', '40', 'Negro/Rojo', 549.00, 4),
  (17, 'Jordan', 'Air Jordan 1 Mid', '41', 'Negro/Rojo', 549.00, 6),
  (18, 'Jordan', 'Air Jordan 1 Mid', '42', 'Negro/Rojo', 549.00, 5),
  (19, 'Jordan', 'Air Jordan 1 Mid', '43', 'Negro/Rojo', 549.00, 7),
  (20, 'Jordan', 'Air Jordan 1 Mid', '44', 'Negro/Rojo', 549.00, 8),
  (21, 'Jordan', 'Air Jordan 4 Retro', '40', 'Blanco/Gris', 899.00, 5),
  (22, 'Jordan', 'Air Jordan 4 Retro', '41', 'Blanco/Gris', 899.00, 7),
  (23, 'Jordan', 'Air Jordan 4 Retro', '42', 'Blanco/Gris', 899.00, 5),
  (24, 'Jordan', 'Air Jordan 4 Retro', '43', 'Blanco/Gris', 899.00, 7),
  (25, 'Jordan', 'Air Jordan 4 Retro', '44', 'Blanco/Gris', 899.00, 3),
  (26, 'Nike', 'Air Max 90', '39', 'Negro', 499.00, 0),
  (27, 'Nike', 'Air Max 90', '40', 'Negro', 499.00, 6),
  (28, 'Nike', 'Air Max 90', '41', 'Negro', 499.00, 4),
  (29, 'Nike', 'Air Max 90', '42', 'Negro', 499.00, 3),
  (30, 'Nike', 'Air Max 90', '43', 'Negro', 499.00, 5),
  (31, 'Nike', 'Air Max Plus', '39', 'Negro', 749.00, 8),
  (32, 'Nike', 'Air Max Plus', '40', 'Negro', 749.00, 2),
  (33, 'Nike', 'Air Max Plus', '41', 'Negro', 749.00, 6),
  (34, 'Nike', 'Air Max Plus', '42', 'Negro', 749.00, 0),
  (35, 'Nike', 'Air Max Plus', '43', 'Negro', 749.00, 9),
  (36, 'Jordan', 'Air Jordan 1 Low', '37', 'Blanco/Azul', 479.00, 6),
  (37, 'Jordan', 'Air Jordan 1 Low', '38', 'Blanco/Azul', 479.00, 6),
  (38, 'Jordan', 'Air Jordan 1 Low', '39', 'Blanco/Azul', 479.00, 8),
  (39, 'Jordan', 'Air Jordan 1 Low', '40', 'Blanco/Azul', 479.00, 2),
  (40, 'Jordan', 'Air Jordan 1 Low', '41', 'Blanco/Azul', 479.00, 3),
  (41, 'Jordan', 'Air Jordan 1 Low', '42', 'Blanco/Azul', 479.00, 3),
  (42, 'Nike', 'Air Zoom Pegasus 41', '39', 'Gris', 529.00, 4),
  (43, 'Nike', 'Air Zoom Pegasus 41', '40', 'Gris', 529.00, 2),
  (44, 'Nike', 'Air Zoom Pegasus 41', '41', 'Gris', 529.00, 2),
  (45, 'Nike', 'Air Zoom Pegasus 41', '42', 'Gris', 529.00, 7),
  (46, 'Nike', 'Air Zoom Pegasus 41', '43', 'Gris', 529.00, 8),
  (47, 'Nike', 'Tech Fleece Hoodie', 'S', 'Negro', 429.00, 7),
  (48, 'Nike', 'Tech Fleece Hoodie', 'M', 'Negro', 429.00, 6),
  (49, 'Nike', 'Tech Fleece Hoodie', 'L', 'Negro', 429.00, 5),
  (50, 'Nike', 'Tech Fleece Hoodie', 'XL', 'Negro', 429.00, 2),
  (51, 'Nike', 'Polo Sportswear Club', 'S', 'Blanco', 119.00, 8),
  (52, 'Nike', 'Polo Sportswear Club', 'M', 'Blanco', 119.00, 2),
  (53, 'Nike', 'Polo Sportswear Club', 'L', 'Blanco', 119.00, 0),
  (54, 'Nike', 'Polo Sportswear Club', 'XL', 'Blanco', 119.00, 4),
  (55, 'Jordan', 'Essentials Hoodie', 'S', 'Gris', 329.00, 10),
  (56, 'Jordan', 'Essentials Hoodie', 'M', 'Gris', 329.00, 5),
  (57, 'Jordan', 'Essentials Hoodie', 'L', 'Gris', 329.00, 7),
  (58, 'Jordan', 'Essentials Hoodie', 'XL', 'Gris', 329.00, 5),
  (59, 'Jordan', 'Short Jumpman', 'S', 'Negro', 149.00, 3),
  (60, 'Jordan', 'Short Jumpman', 'M', 'Negro', 149.00, 2),
  (61, 'Jordan', 'Short Jumpman', 'L', 'Negro', 149.00, 5),
  (62, 'Jordan', 'Short Jumpman', 'XL', 'Negro', 149.00, 7);

INSERT INTO movimiento_inventario (id_producto, id_usuario, tipo, cantidad, fecha) VALUES
  (1, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (2, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (3, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (4, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (5, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (6, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (7, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (8, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (9, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (10, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (11, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (12, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (13, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (14, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (15, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (16, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (17, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (18, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (19, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (20, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (21, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (22, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (23, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (24, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (25, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (26, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (27, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (28, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (29, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (30, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (31, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (32, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (33, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (34, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (35, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (36, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (37, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (38, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (39, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (40, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (41, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (42, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (43, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (44, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (45, 1, 'entrada', 9, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (46, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (47, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (48, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (49, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (50, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (51, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (52, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (53, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (54, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (55, 1, 'entrada', 7, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (56, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (57, 1, 'entrada', 5, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (58, 1, 'entrada', 6, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (59, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (60, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (61, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (62, 1, 'entrada', 8, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 35 DAY), INTERVAL -9 HOUR)),
  (37, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 17 DAY), INTERVAL -10 HOUR)),
  (20, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL -10 HOUR)),
  (2, 1, 'entrada', 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 14 DAY), INTERVAL -10 HOUR)),
  (55, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 20 DAY), INTERVAL -10 HOUR)),
  (46, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL -10 HOUR)),
  (24, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 16 DAY), INTERVAL -10 HOUR)),
  (57, 1, 'entrada', 3, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 14 DAY), INTERVAL -10 HOUR)),
  (30, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL -10 HOUR)),
  (28, 1, 'entrada', 4, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 16 DAY), INTERVAL -10 HOUR)),
  (6, 1, 'entrada', 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 17 DAY), INTERVAL -10 HOUR)),
  (6, 1, 'salida', 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 12 DAY), INTERVAL -18 HOUR));

INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (1, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 29 DAY), INTERVAL -16 HOUR) + INTERVAL 5 MINUTE, 'Local', 549.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (1, 19, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (2, 3, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 29 DAY), INTERVAL -17 HOUR) + INTERVAL 54 MINUTE, 'Local', 1757.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (2, 24, 1, 899.00), (2, 49, 2, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (3, 4, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 28 DAY), INTERVAL -12 HOUR) + INTERVAL 32 MINUTE, 'Local', 1976.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (3, 13, 1, 459.00), (3, 45, 2, 529.00), (3, 9, 1, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (4, 7, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 28 DAY), INTERVAL -16 HOUR) + INTERVAL 33 MINUTE, 'TikTok', 1607.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (4, 44, 2, 529.00), (4, 17, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (5, 4, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 27 DAY), INTERVAL -14 HOUR) + INTERVAL 38 MINUTE, 'WhatsApp', 1248.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (5, 27, 1, 499.00), (5, 32, 1, 749.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (6, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 27 DAY), INTERVAL -19 HOUR) + INTERVAL 20 MINUTE, 'WhatsApp', 578.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (6, 60, 1, 149.00), (6, 50, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (7, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 26 DAY), INTERVAL -12 HOUR) + INTERVAL 31 MINUTE, 'Local', 479.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (7, 41, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (8, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 26 DAY), INTERVAL -17 HOUR) + INTERVAL 20 MINUTE, 'WhatsApp', 298.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (8, 60, 1, 149.00), (8, 59, 1, 149.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (9, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 26 DAY), INTERVAL -18 HOUR) + INTERVAL 42 MINUTE, 'Local', 908.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (9, 36, 1, 479.00), (9, 3, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (10, 5, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 26 DAY), INTERVAL -19 HOUR) + INTERVAL 21 MINUTE, 'WhatsApp', 2716.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (10, 12, 2, 459.00), (10, 24, 1, 899.00), (10, 25, 1, 899.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (11, 8, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL -12 HOUR) + INTERVAL 23 MINUTE, 'WhatsApp', 459.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (11, 12, 1, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (12, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL -15 HOUR) + INTERVAL 11 MINUTE, 'WhatsApp', 429.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (12, 47, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (13, 4, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL -16 HOUR) + INTERVAL 4 MINUTE, 'TikTok', 1777.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (13, 21, 1, 899.00), (13, 55, 1, 329.00), (13, 16, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (14, 1, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL -18 HOUR) + INTERVAL 35 MINUTE, 'Local', 119.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (14, 53, 1, 119.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (15, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 24 DAY), INTERVAL -17 HOUR) + INTERVAL 55 MINUTE, 'Local', 1847.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (15, 16, 2, 549.00), (15, 34, 1, 749.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (16, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 23 DAY), INTERVAL -13 HOUR) + INTERVAL 51 MINUTE, 'WhatsApp', 149.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (16, 61, 1, 149.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (17, 4, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 23 DAY), INTERVAL -13 HOUR) + INTERVAL 11 MINUTE, 'TikTok', 479.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (17, 37, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (18, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 23 DAY), INTERVAL -14 HOUR) + INTERVAL 48 MINUTE, 'WhatsApp', 479.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (18, 36, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (19, 2, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 23 DAY), INTERVAL -16 HOUR) + INTERVAL 16 MINUTE, 'WhatsApp', 1616.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (19, 7, 2, 429.00), (19, 2, 1, 429.00), (19, 57, 1, 329.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (20, 1, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 23 DAY), INTERVAL -18 HOUR) + INTERVAL 55 MINUTE, 'Local', 988.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (20, 46, 1, 529.00), (20, 10, 1, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (21, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 DAY), INTERVAL -10 HOUR) + INTERVAL 33 MINUTE, 'WhatsApp', 1008.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (21, 9, 1, 459.00), (21, 18, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (22, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 DAY), INTERVAL -12 HOUR) + INTERVAL 33 MINUTE, 'TikTok', 2277.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (22, 23, 1, 899.00), (22, 21, 1, 899.00), (22, 39, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (23, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 22 DAY), INTERVAL -16 HOUR) + INTERVAL 55 MINUTE, 'Facebook', 149.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (23, 60, 1, 149.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (24, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 21 DAY), INTERVAL -18 HOUR) + INTERVAL 24 MINUTE, 'Facebook', 908.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (24, 6, 1, 429.00), (24, 37, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (25, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 21 DAY), INTERVAL -19 HOUR) + INTERVAL 40 MINUTE, 'Local', 549.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (25, 16, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (26, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 20 DAY), INTERVAL -15 HOUR) + INTERVAL 6 MINUTE, 'TikTok', 479.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (26, 39, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (27, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 20 DAY), INTERVAL -19 HOUR) + INTERVAL 59 MINUTE, 'Local', 459.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (27, 10, 1, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (28, 8, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 19 DAY), INTERVAL -13 HOUR) + INTERVAL 38 MINUTE, 'WhatsApp', 899.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (28, 23, 1, 899.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (29, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 19 DAY), INTERVAL -14 HOUR) + INTERVAL 13 MINUTE, 'Local', 628.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (29, 60, 1, 149.00), (29, 40, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (30, 2, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 19 DAY), INTERVAL -15 HOUR) + INTERVAL 1 MINUTE, 'WhatsApp', 899.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (30, 21, 1, 899.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (31, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 18 DAY), INTERVAL -10 HOUR) + INTERVAL 29 MINUTE, 'Facebook', 1527.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (31, 18, 1, 549.00), (31, 41, 1, 479.00), (31, 30, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (32, 5, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 18 DAY), INTERVAL -14 HOUR) + INTERVAL 15 MINUTE, 'WhatsApp', 549.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (32, 20, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (33, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 18 DAY), INTERVAL -15 HOUR) + INTERVAL 13 MINUTE, 'Local', 1037.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (33, 52, 1, 119.00), (33, 11, 2, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (34, 6, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 18 DAY), INTERVAL -16 HOUR) + INTERVAL 27 MINUTE, 'TikTok', 749.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (34, 31, 1, 749.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (35, 4, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 17 DAY), INTERVAL -14 HOUR) + INTERVAL 32 MINUTE, 'Local', 499.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (35, 30, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (36, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 16 DAY), INTERVAL -15 HOUR) + INTERVAL 54 MINUTE, 'WhatsApp', 1008.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (36, 9, 1, 459.00), (36, 18, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (37, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 16 DAY), INTERVAL -19 HOUR) + INTERVAL 33 MINUTE, 'Local', 529.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (37, 44, 1, 529.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (38, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL -16 HOUR) + INTERVAL 8 MINUTE, 'WhatsApp', 479.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (38, 41, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (39, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL -18 HOUR) + INTERVAL 32 MINUTE, 'Local', 1407.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (39, 28, 1, 499.00), (39, 39, 1, 479.00), (39, 48, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (40, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 14 DAY), INTERVAL -18 HOUR) + INTERVAL 13 MINUTE, 'Local', 1008.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (40, 10, 1, 459.00), (40, 16, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (41, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 14 DAY), INTERVAL -18 HOUR) + INTERVAL 50 MINUTE, 'Local', 429.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (41, 4, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (42, 4, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 14 DAY), INTERVAL -18 HOUR) + INTERVAL 55 MINUTE, 'Facebook', 119.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (42, 53, 1, 119.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (43, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 13 DAY), INTERVAL -11 HOUR) + INTERVAL 17 MINUTE, 'WhatsApp', 749.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (43, 34, 1, 749.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (44, 8, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 13 DAY), INTERVAL -12 HOUR) + INTERVAL 49 MINUTE, 'Facebook', 499.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (44, 28, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (45, 5, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 12 DAY), INTERVAL -11 HOUR) + INTERVAL 55 MINUTE, 'WhatsApp', 549.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (45, 14, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (46, 8, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 12 DAY), INTERVAL -17 HOUR) + INTERVAL 45 MINUTE, 'TikTok', 549.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (46, 17, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (47, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 12 DAY), INTERVAL -19 HOUR) + INTERVAL 31 MINUTE, 'WhatsApp', 499.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (47, 30, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (48, 4, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 11 DAY), INTERVAL -11 HOUR) + INTERVAL 8 MINUTE, 'Local', 1228.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (48, 58, 1, 329.00), (48, 22, 1, 899.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (49, 1, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 11 DAY), INTERVAL -15 HOUR) + INTERVAL 35 MINUTE, 'Local', 858.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (49, 7, 1, 429.00), (49, 49, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (50, 6, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 11 DAY), INTERVAL -18 HOUR) + INTERVAL 43 MINUTE, 'Facebook', 899.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (50, 22, 1, 899.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (51, 7, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 10 DAY), INTERVAL -16 HOUR) + INTERVAL 53 MINUTE, 'Local', 459.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (51, 11, 1, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (52, 2, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 10 DAY), INTERVAL -17 HOUR) + INTERVAL 54 MINUTE, 'WhatsApp', 1048.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (52, 19, 1, 549.00), (52, 26, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (53, 5, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 9 DAY), INTERVAL -10 HOUR) + INTERVAL 20 MINUTE, 'WhatsApp', 1028.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (53, 14, 1, 549.00), (53, 37, 1, 479.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (54, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 9 DAY), INTERVAL -14 HOUR) + INTERVAL 23 MINUTE, 'WhatsApp', 797.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (54, 60, 2, 149.00), (54, 28, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (55, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 9 DAY), INTERVAL -14 HOUR) + INTERVAL 41 MINUTE, 'Local', 268.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (55, 54, 1, 119.00), (55, 61, 1, 149.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (56, 2, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 9 DAY), INTERVAL -15 HOUR) + INTERVAL 25 MINUTE, 'WhatsApp', 698.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (56, 61, 1, 149.00), (56, 18, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (57, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 9 DAY), INTERVAL -16 HOUR) + INTERVAL 34 MINUTE, 'WhatsApp', 1048.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (57, 26, 1, 499.00), (57, 20, 1, 549.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (58, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL -12 HOUR) + INTERVAL 39 MINUTE, 'WhatsApp', 429.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (58, 2, 1, 429.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (59, 1, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL -17 HOUR) + INTERVAL 46 MINUTE, 'Local', 2086.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (59, 46, 1, 529.00), (59, 26, 1, 499.00), (59, 43, 2, 529.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (60, 7, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL -18 HOUR) + INTERVAL 34 MINUTE, 'WhatsApp', 149.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (60, 62, 1, 149.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (61, 3, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL -19 HOUR) + INTERVAL 12 MINUTE, 'WhatsApp', 119.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (61, 53, 1, 119.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (62, 1, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 7 DAY), INTERVAL -17 HOUR) + INTERVAL 9 MINUTE, 'Local', 119.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (62, 54, 1, 119.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (63, 6, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 6 DAY), INTERVAL -13 HOUR) + INTERVAL 38 MINUTE, 'Local', 749.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (63, 34, 1, 749.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (64, 9, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 6 DAY), INTERVAL -15 HOUR) + INTERVAL 35 MINUTE, 'Local', 499.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (64, 30, 1, 499.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (65, 7, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 6 DAY), INTERVAL -16 HOUR) + INTERVAL 45 MINUTE, 'Facebook', 119.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (65, 54, 1, 119.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (66, 6, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 4 DAY), INTERVAL -16 HOUR) + INTERVAL 9 MINUTE, 'WhatsApp', 459.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (66, 9, 1, 459.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (67, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL -13 HOUR) + INTERVAL 46 MINUTE, 'Facebook', 238.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (67, 52, 2, 119.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (68, 6, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL -10 HOUR) + INTERVAL 9 MINUTE, 'Facebook', 529.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (68, 43, 1, 529.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (69, 2, 1, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 2 DAY), INTERVAL -18 HOUR) + INTERVAL 8 MINUTE, 'Facebook', 529.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (69, 42, 1, 529.00);
INSERT INTO venta (id_venta, id_cliente, id_usuario, fecha, canal, total) VALUES (70, 2, 2, DATE_SUB(DATE_SUB(CURDATE(), INTERVAL 1 DAY), INTERVAL -11 HOUR) + INTERVAL 51 MINUTE, 'Local', 899.00);
INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (70, 21, 1, 899.00);
