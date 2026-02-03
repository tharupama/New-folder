-- Create database
CREATE DATABASE IF NOT EXISTS shop_db;
USE shop_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 50,
    category VARCHAR(50) NOT NULL,
    rating DECIMAL(3, 1) DEFAULT 4.5,
    tag VARCHAR(50),
    is_available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample products
INSERT INTO products (name, category, price, rating, tag, image, description, stock) VALUES
('Aurora Smart Speaker', 'tech', 129.99, 4.7, 'Bestseller', 'https://images.unsplash.com/photo-1512446816042-444d641267d4?auto=format&fit=crop&w=800&q=80', 'Premium wireless speaker with superior sound quality', 72),
('Nimbus Air Purifier', 'home', 249.00, 4.9, 'Eco', 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80', 'Advanced air purification for cleaner air', 45),
('Pulse Fitness Band', 'fitness', 89.50, 4.6, 'New', 'https://images.unsplash.com/photo-1518441902117-fc8b0c201d19?auto=format&fit=crop&w=800&q=80', 'Track your fitness with precision', 58),
('Lumen Desk Lamp', 'home', 58.00, 4.4, 'Limited', 'https://images.unsplash.com/photo-1481277542470-605612bd2d61?auto=format&fit=crop&w=800&q=80', 'Energy-efficient LED desk lamp', 34),
('Stride Eco Sneakers', 'fashion', 145.00, 4.5, 'Trending', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80', 'Sustainable and comfortable footwear', 62),
('Vista Travel Backpack', 'fashion', 110.00, 4.3, 'Travel', 'https://images.unsplash.com/photo-1514474959185-1472d4e46f4e?auto=format&fit=crop&w=800&q=80', 'Perfect companion for your travels', 28),
('Halo Aroma Diffuser', 'home', 64.99, 4.8, 'Relax', 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=800&q=80', 'Create a peaceful atmosphere at home', 50),
('Zen Smart Watch', 'tech', 219.00, 4.7, 'Editor pick', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80', 'Advanced health and fitness tracking', 41);

