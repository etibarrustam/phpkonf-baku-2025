CREATE DATABASE IF NOT EXISTS plov_express;
USE plov_express;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    payment_status VARCHAR(50) DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_status (status, created_at),
    INDEX idx_payment_status (payment_status)
);

INSERT INTO customers (name, email, phone, address) VALUES
('Nigar Əliyeva', 'nigar@example.com', '+994501234567', 'Baku, Nizami street 15'),
('Rəşad Məmmədov', 'rashad@example.com', '+994551234567', 'Baku, 28 May street 25'),
('Leyla Həsənova', 'leyla@example.com', '+994701234567', 'Baku, Neftchilar avenue 40');

INSERT INTO products (name, description, price, is_available) VALUES
('Toyuq Plov', 'Traditional Azerbaijani pilaf with chicken', 12.00, TRUE),
('Qoyun Plov', 'Traditional Azerbaijani pilaf with lamb', 15.00, TRUE),
('Şah Plov', 'Royal pilaf with dried fruits and nuts', 18.00, TRUE),
('Balıq Plov', 'Pilaf with fish', 14.00, TRUE);
