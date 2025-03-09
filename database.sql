-- Create the database
CREATE DATABASE IF NOT EXISTS art_delivery;
USE art_delivery;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    user_type ENUM('user', 'admin') DEFAULT 'user',
    google_id VARCHAR(255) UNIQUE,
    google_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255)
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'on_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('cash', 'card', 'upi') DEFAULT 'cash',
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    order_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Coupons table
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_discount_amount DECIMAL(10,2),
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    usage_limit INT DEFAULT NULL,
    times_used INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add coupon_id to orders table
ALTER TABLE orders ADD COLUMN coupon_id INT NULL;
ALTER TABLE orders ADD FOREIGN KEY (coupon_id) REFERENCES coupons(id);
ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0;

-- Insert default admin user
INSERT INTO users (username, email, password, full_name, user_type) 
VALUES ('admin', 'admin@art.com', 'admin123', 'System Admin', 'admin');

-- Insert some sample categories
INSERT INTO categories (name, description, image) 
VALUES 
    ('Paintings', 'Beautiful handmade and digital paintings', 'https://th.bing.com/th/id/OIP.i5u3jY75NWyGkYpjvcBxXwHaNu?pid=ImgDet&w=184&h=340&c=7'),
    ('Canvas Art', 'High-quality canvas prints and wall decor', 'https://www.bing.com/th?id=OIP.6ic2QWM0yJ3LbiF5DDa0wAHaHa&w=155&h=200&c=8&rs=1&qlt=90&o=6&pid=3.1&rm=2'),
    ('Digital Artwork', 'Modern digital illustrations and NFTs', 'https://th.bing.com/th/id/OIF.ftqXvaYlaehGOUT2HeuzDQ?w=271&h=180&c=7&r=0&o=5&pid=1.7');
