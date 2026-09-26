-- Database: restaurant_4to9
-- Restaurant Management System for 4 TO 9

-- Create Database
CREATE DATABASE IF NOT EXISTS restaurant_4to9;
USE restaurant_4to9;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menu Categories Table
CREATE TABLE IF NOT EXISTS menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
);

-- Restaurant Tables Table
CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10) NOT NULL UNIQUE,
    table_name VARCHAR(100),
    capacity INT NOT NULL,
    location VARCHAR(100),
    status ENUM('active', 'inactive', 'maintenance') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    table_id INT,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    guest_count INT NOT NULL,
    special_request TEXT,
    status ENUM('pending', 'confirmed', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE SET NULL
);

-- Restaurant Settings Table
CREATE TABLE IF NOT EXISTS restaurant_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_name VARCHAR(100) NOT NULL DEFAULT '4 TO 9',
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    opening_time TIME DEFAULT '10:00:00',
    closing_time TIME DEFAULT '23:00:00',
    booking_duration INT DEFAULT 90 COMMENT 'Duration in minutes',
    max_booking_guests INT DEFAULT 20,
    allow_online_booking TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Gallery Table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    image VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Backups Table
CREATE TABLE IF NOT EXISTS backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_size VARCHAR(50),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Admin Account
-- Password: admin123 (hashed with password_hash)
INSERT INTO users (name, email, phone, password, role, status) VALUES
('Admin User', 'admin@4to9.com', '9876543210', '$2y$10$aXb1v83WMqEKU/30QfUd9OQ6PLFproDDP1AkQpxXe1XsKBhJnWewG', 'admin', 'active');

-- Insert Default Restaurant Settings
INSERT INTO restaurant_settings (restaurant_name, phone, email, address, opening_time, closing_time, booking_duration, max_booking_guests, allow_online_booking) VALUES
('4 TO 9', '+91 98765 43210', 'info@4to9.com', '123 Restaurant Street, City, State', '10:00:00', '23:00:00', 90, 20, 1);

-- Insert Sample Menu Categories
INSERT INTO menu_categories (name, description, status) VALUES
('Starters', 'Appetizers and finger foods', 'active'),
('Main Course', 'Main dishes and entrees', 'active'),
('Pizza', 'Various types of pizza', 'active'),
('Burger', 'Gourmet burgers', 'active'),
('Pasta', 'Italian pasta dishes', 'active'),
('Drinks', 'Beverages and cocktails', 'active'),
('Desserts', 'Sweet treats and desserts', 'active');

-- Insert Sample Menu Items
INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured) VALUES
(1, 'Spring Rolls', 'Crispy vegetable spring rolls with sweet chili sauce', 150, 1, 0),
(1, 'Garlic Bread', 'Toasted bread with garlic butter and herbs', 120, 1, 0),
(2, 'Grilled Chicken', 'Tender grilled chicken with herbs and vegetables', 450, 1, 1),
(2, 'Fish Curry', 'Traditional fish curry with rice', 380, 1, 0),
(3, 'Margherita Pizza', 'Classic pizza with tomato sauce and mozzarella', 350, 1, 1),
(3, 'Pepperoni Pizza', 'Pizza with pepperoni and cheese', 420, 1, 0),
(4, 'Chicken Burger', 'Grilled chicken patty with cheese and vegetables', 450, 1, 1),
(4, 'Veggie Burger', 'Vegetarian patty with fresh vegetables', 380, 1, 0),
(5, 'Spaghetti Carbonara', 'Creamy pasta with bacon and parmesan', 420, 1, 0),
(5, 'Penne Arrabiata', 'Spicy tomato pasta with garlic and chili', 350, 1, 0),
(6, 'Fresh Lime Soda', 'Refreshing lime soda with mint', 80, 1, 0),
(6, 'Mango Smoothie', 'Fresh mango smoothie', 120, 1, 0),
(7, 'Chocolate Brownie', 'Warm chocolate brownie with ice cream', 180, 1, 1),
(7, 'Ice Cream Sundae', 'Vanilla ice cream with toppings', 150, 1, 0);

-- Kids' favourites can be applied again to an existing restaurant database.
INSERT INTO menu_categories (name, description, status)
SELECT 'Kids'' Favorites', 'Smaller portions of familiar family favourites', 'active'
WHERE NOT EXISTS (SELECT 1 FROM menu_categories WHERE name = 'Kids'' Favorites');

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Little Margherita Pizza', 'A smaller mozzarella and tomato pizza with basil, baked until golden.', 250, 1, 0
FROM menu_categories WHERE name = 'Kids'' Favorites'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Little Margherita Pizza' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Mini Cheeseburger', 'A mini cheese burger with lettuce and a side of fries.', 260, 1, 0
FROM menu_categories WHERE name = 'Kids'' Favorites'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Mini Cheeseburger' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Creamy Mac & Cheese', 'Shell pasta in a creamy cheese sauce with a mild, comforting flavour.', 230, 1, 0
FROM menu_categories WHERE name = 'Kids'' Favorites'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Creamy Mac & Cheese' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Mini Sundae', 'A smaller vanilla sundae with chocolate sauce and a crisp wafer.', 150, 1, 0
FROM menu_categories WHERE name = 'Kids'' Favorites'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Mini Sundae' AND category_id = menu_categories.id);

-- Momo and fast food samples can be applied again to an existing restaurant database.
-- Admins can edit these categories and their items through the regular menu panel.
INSERT INTO menu_categories (name, description, status)
SELECT 'Momo', 'Steamed and fried dumplings served with house dipping sauce', 'active'
WHERE NOT EXISTS (SELECT 1 FROM menu_categories WHERE name = 'Momo');

INSERT INTO menu_categories (name, description, status)
SELECT 'Fast Food', 'Freshly prepared favourites for a quick bite', 'active'
WHERE NOT EXISTS (SELECT 1 FROM menu_categories WHERE name = 'Fast Food');

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Veg Steamed Momo', 'Tender steamed dumplings filled with seasoned vegetables, served with dipping sauce.', 180, 1, 0
FROM menu_categories WHERE name = 'Momo'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Veg Steamed Momo' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Crispy Fried Momo', 'Golden fried dumplings with a crisp shell and a spicy dipping sauce.', 220, 1, 0
FROM menu_categories WHERE name = 'Momo'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Crispy Fried Momo' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Momo Platter', 'A shareable assortment of steamed, pan-seared and fried dumplings.', 320, 1, 0
FROM menu_categories WHERE name = 'Momo'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Momo Platter' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Loaded Fries', 'Crisp fries covered with cheese sauce, savoury bits and fresh herbs.', 220, 1, 0
FROM menu_categories WHERE name = 'Fast Food'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Loaded Fries' AND category_id = menu_categories.id);

INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured)
SELECT id, 'Chicken Wrap', 'Soft tortilla filled with chicken, crunchy vegetables and creamy sauce.', 290, 1, 0
FROM menu_categories WHERE name = 'Fast Food'
AND NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'Chicken Wrap' AND category_id = menu_categories.id);

-- Insert Sample Tables
INSERT INTO restaurant_tables (table_number, table_name, capacity, location, status) VALUES
('01', 'Window Table', 2, 'First Floor', 'active'),
('02', 'Corner Table', 4, 'First Floor', 'active'),
('03', 'Center Table', 4, 'First Floor', 'active'),
('04', 'Window Table', 6, 'First Floor', 'active'),
('05', 'Private Area', 8, 'Second Floor', 'active'),
('06', 'Garden Table', 4, 'Outdoor', 'active'),
('07', 'Terrace Table', 2, 'Outdoor', 'active'),
('08', 'VIP Room', 10, 'Second Floor', 'active'),
('09', 'Family Table', 6, 'First Floor', 'active'),
('10', 'Bar Table', 2, 'Ground Floor', 'active');

-- Insert Sample Gallery Images
INSERT INTO gallery (title, image, status) VALUES
('Restaurant Interior', 'restaurant_interior.jpg', 'active'),
('Delicious Food', 'delicious_food.jpg', 'active'),
('Ambience', 'restaurant_ambience.jpg', 'active'),
('Chef Cooking', 'chef_cooking.jpg', 'active'),
('Window-side Dining', 'table_window.jpg', 'active'),
('Outdoor Dining', 'outdoor_dining.jpg', 'active'),
('A Table for the Occasion', 'dining_table.jpg', 'active'),
('Bar Seating', 'bar_seating.jpg', 'active');
