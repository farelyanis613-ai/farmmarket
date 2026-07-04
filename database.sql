CREATE DATABASE IF NOT EXISTS farmmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE farmmarket;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'farmer', 'delivery', 'admin') NOT NULL DEFAULT 'client',
    phone VARCHAR(20),
    address TEXT,
    farm_name VARCHAR(255),
    created_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    image VARCHAR(255),
    created_at DATETIME NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    delivery_type VARCHAR(50) NOT NULL DEFAULT 'home',
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_address TEXT NULL,
    latitude VARCHAR(64) NULL,
    longitude VARCHAR(64) NULL,
    delivery_person_id INT NULL,
    status VARCHAR(50) NOT NULL,
    failed_reason TEXT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_person_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    paid_at DATETIME NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role, phone, address, farm_name, created_at) VALUES
('Admin Farmmarket', 'admin@farmmarket.test', '$2y$10$e0N0kQjg/8Rk6XJ97Na5vu1I6c2n5uTqP6rF9bju6L4V6P7kYVjCG', 'admin', NULL, NULL, NULL, NOW()),
('Client Farmmarket', 'client@farmmarket.test', '$2y$10$T7W4HU7qz3nQ9c0MhfTempw1A4E5Hj/8E9PwxSsOaF4r8O3nY4G5a', 'client', NULL, NULL, NULL, NOW()),
('GOKOUN Renaud', 'eleveur@gmail.com', '$2y$10$9kUhw8QMHaMe8ZHENtXVrOM0fe8kev402Dtro2iUDr/ril3aGuWTy', 'farmer', NULL, NULL, NULL, NOW());

INSERT INTO categories (name, created_at) VALUES
('Lapins', NOW()),
('Poulets', NOW()),
('Viande', NOW()),
('Oeuf', NOW());

INSERT INTO products (name, description, price, stock, category_id, image, created_at) VALUES
('Lapin Fermier', 'Lapin élevé en plein air, prêt à cuisiner.', 9500.00, 20, 1, '', NOW()),
('Poulet Label Rouge', 'Poulet de qualité, viande tendre et savoureuse.', 8500.00, 30, 2, '', NOW()),
('Lapin en morceaux', 'Assortiment de morceaux de lapin, idéal pour un plat familial.', 6200.00, 15, 1, '', NOW());
