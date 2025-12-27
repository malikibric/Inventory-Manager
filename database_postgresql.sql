-- =====================================================
-- PostgreSQL Version of Database Schema
-- Converted from MySQL for Render Deployment
-- =====================================================

-- Drop existing tables if they exist
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS suppliers CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- =====================================================
-- Table: users
-- =====================================================
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table: categories
-- =====================================================
CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table: suppliers
-- =====================================================
CREATE TABLE suppliers (
    supplier_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table: products
-- =====================================================
CREATE TABLE products (
    product_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 0,
    category_id INTEGER REFERENCES categories(category_id) ON DELETE SET NULL,
    supplier_id INTEGER REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table: orders
-- =====================================================
CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table: order_items
-- =====================================================
CREATE TABLE order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(order_id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(product_id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Indexes for Performance
-- =====================================================
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_supplier ON products(supplier_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);

-- =====================================================
-- Insert Sample Data
-- =====================================================

-- Insert admin user (password: admin123)
-- Note: You should hash passwords properly using password_hash() in PHP
INSERT INTO users (username, email, password, role) VALUES
('Admin User', 'admin@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Regular User', 'user@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and accessories'),
('Furniture', 'Office and home furniture'),
('Stationery', 'Office supplies and stationery'),
('Tools', 'Hardware and tools');

-- Insert suppliers
INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES
('Tech Supplies Co.', 'John Doe', 'john@techsupplies.com', '+1234567890', '123 Tech Street, Silicon Valley'),
('Furniture Plus', 'Jane Smith', 'jane@furnitureplus.com', '+1234567891', '456 Furniture Ave, Design City'),
('Office Essentials', 'Bob Johnson', 'bob@officeessentials.com', '+1234567892', '789 Office Blvd, Business Town');

-- Insert sample products
INSERT INTO products (name, description, price, quantity, category_id, supplier_id) VALUES
('Laptop HP Pavilion', 'High-performance laptop for business', 899.99, 15, 1, 1),
('Office Chair', 'Ergonomic office chair', 249.50, 8, 2, 2),
('USB-C Cable', 'Fast charging USB-C cable', 12.99, 50, 1, 1),
('Desk Lamp', 'LED desk lamp with adjustable brightness', 34.99, 25, 2, 2),
('Wireless Mouse', 'Bluetooth wireless mouse', 29.99, 40, 1, 1),
('Notebook A4', 'Premium quality notebook', 3.50, 100, 3, 3),
('Monitor Stand', 'Adjustable monitor stand', 45.00, 12, 2, 2),
('Keyboard Mechanical', 'RGB mechanical keyboard', 79.99, 18, 1, 1);

-- Insert sample order
INSERT INTO orders (user_id, total_amount, status) VALUES
(1, 929.98, 'completed');

-- Insert order items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 899.99),
(1, 5, 1, 29.99);

-- =====================================================
-- Create function to update updated_at timestamp
-- =====================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- =====================================================
-- Create triggers for updated_at
-- =====================================================
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_categories_updated_at BEFORE UPDATE ON categories
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_suppliers_updated_at BEFORE UPDATE ON suppliers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_orders_updated_at BEFORE UPDATE ON orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- Grant necessary permissions
-- =====================================================
-- Note: Render will handle permissions automatically
-- These are for reference if you're setting up manually

-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO shop_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO shop_user;

-- =====================================================
-- Verification Queries
-- =====================================================
-- Run these to verify the setup:
-- SELECT * FROM users;
-- SELECT * FROM categories;
-- SELECT * FROM products;
-- SELECT COUNT(*) as total_products FROM products;
