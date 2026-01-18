--  pizza_web_baza.sql
--  Baza za aplikaciju "Pizza Web"
--  Sadrži početne tablice, podatke, admin korisnika i demo narudžbu.
--
--  Nakon importa možeš se prijaviti kao:
--     email:    admin@pizza.local
--     lozinka:  admin123


-- 1) Kreiraj bazu ako ne postoji i prebaci se na nju
CREATE DATABASE IF NOT EXISTS pizza_web
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pizza_web;

-- 2) Sigurno obriši eventualne stare tablice (redoslijed zbog FK-veza)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS pizzas;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 3) Tablica korisnika (admin + ostali zaposlenici)
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  pass_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 4) Kategorije pizza (npr. Klasične / Specijalne / Vegetarijanske)
CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 5) Pizze
CREATE TABLE pizzas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  description TEXT NULL,
  price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  image VARCHAR(255) NULL,
  CONSTRAINT fk_pizzas_category
    FOREIGN KEY (category_id)
    REFERENCES categories(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 6) Narudžbe kupaca
--    postcode = poštanski broj
--    order_secret = tajni token za praćenje narudžbe
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(255) NOT NULL,
  phone VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  postcode VARCHAR(16) DEFAULT NULL,
  total DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(32) NOT NULL DEFAULT 'pending',
  order_secret VARCHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 7) Stavke narudžbi
--    unit_price = cijena u trenutku narudžbe
CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  pizza_id INT UNSIGNED NOT NULL,
  qty INT UNSIGNED NOT NULL,
  unit_price DECIMAL(8,2) NOT NULL,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id)
    REFERENCES orders(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_order_items_pizza
    FOREIGN KEY (pizza_id)
    REFERENCES pizzas(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;



--  Početni podaci 


-- Admin korisnik
-- Lozinka: admin123
-- Hash je bcrypt (password_hash u PHP-u), pa će password_verify() raditi odmah.
INSERT INTO users (email, pass_hash, is_admin)
VALUES (
  'admin@pizza.local',
  '$2y$10$QnS2s6b2yE1b5r3M8k1H7uE1m2pC4s7fC1o1y1m7cP0Y7a4p1g8wy',
  1
);

-- Kategorije
INSERT INTO categories (name) VALUES
 ('Klasične'),
 ('Specijalne'),
 ('Vegetarijanske');

-- Pizze (ID-jevi će biti 1,2,3,4,5 redom)
INSERT INTO pizzas (category_id, name, description, price, image) VALUES
 (1, 'Margherita', 'Rajčica, mozzarella, bosiljak', 6.50, 'margherita.jpg'),
 (1, 'Funghi', 'Gljive, mozzarella', 7.20, 'funghi.jpg'),
 (2, 'Capricciosa', 'Šunka, gljive, artičoke, masline', 8.90, 'capricciosa.jpg'),
 (2, 'Diavola', 'Ljuta salama, čili, mozzarella', 9.20, 'diavola.jpg'),
 (3, 'Veggie Mix', 'Paprika, luk, tikvice, kukuruz', 8.00, 'veggie.jpg');

-- Primjer narudžba #1
-- Primjer kupca iz Karlovca (47000), naručio:
--   2x Margherita (id 1 po 6.50)
--   1x Capricciosa (id 3 po 8.90)
-- Ukupno = (2 * 6.50) + (1 * 8.90) = 13.00 + 8.90 = 21.90
INSERT INTO orders (
  id,
  customer_name,
  phone,
  city,
  postcode,
  total,
  status,
  order_secret,
  created_at
) VALUES (
  1,
  'Test Kupac',
  '091 000 111',
  'Karlovac',
  '47000',
  21.90,
  'pending',
  'abcdef012345abcdef678901',
  NOW()
);

INSERT INTO order_items (order_id, pizza_id, qty, unit_price) VALUES
 (1, 1, 2, 6.50),  -- 2x Margherita
 (1, 3, 1, 8.90);  -- 1x Capricciosa
