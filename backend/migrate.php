<?php
/**
 * Database Migration Script
 * Visit: https://inventory-manager-2.onrender.com/backend/migrate.php
 * 
 * WARNING: This will drop all existing tables and data!
 */

// Security: Only allow in non-production or with secret key
$secret = $_GET['secret'] ?? '';
$expectedSecret = getenv('MIGRATION_SECRET') ?: 'migrate123'; // Change this!

if ($secret !== $expectedSecret) {
    die(json_encode([
        'success' => false,
        'error' => 'Unauthorized. Provide correct secret parameter.'
    ]));
}

require_once __DIR__ . '/config/config.php';

try {
    $conn = Database::connect();
    
    // Start transaction
    $conn->beginTransaction();
    
    echo "Starting migration...\n\n";
    
    // Drop existing tables
    echo "Dropping existing tables...\n";
    $conn->exec("DROP TABLE IF EXISTS order_items CASCADE");
    $conn->exec("DROP TABLE IF EXISTS orders CASCADE");
    $conn->exec("DROP TABLE IF EXISTS products CASCADE");
    $conn->exec("DROP TABLE IF EXISTS categories CASCADE");
    $conn->exec("DROP TABLE IF EXISTS suppliers CASCADE");
    $conn->exec("DROP TABLE IF EXISTS users CASCADE");
    echo "✓ Tables dropped\n\n";
    
    // Create users table
    echo "Creating users table...\n";
    $conn->exec("
        CREATE TABLE users (
            user_id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Users table created\n";
    
    // Create categories table
    echo "Creating categories table...\n";
    $conn->exec("
        CREATE TABLE categories (
            category_id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Categories table created\n";
    
    // Create suppliers table
    echo "Creating suppliers table...\n";
    $conn->exec("
        CREATE TABLE suppliers (
            supplier_id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            contact_person VARCHAR(100),
            email VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Suppliers table created\n";
    
    // Create products table
    echo "Creating products table...\n";
    $conn->exec("
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
        )
    ");
    echo "✓ Products table created\n";
    
    // Create orders table
    echo "Creating orders table...\n";
    $conn->exec("
        CREATE TABLE orders (
            order_id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
            total_amount DECIMAL(10, 2) NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Orders table created\n";
    
    // Create order_items table
    echo "Creating order_items table...\n";
    $conn->exec("
        CREATE TABLE order_items (
            order_item_id SERIAL PRIMARY KEY,
            order_id INTEGER REFERENCES orders(order_id) ON DELETE CASCADE,
            product_id INTEGER REFERENCES products(product_id) ON DELETE CASCADE,
            quantity INTEGER NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Order_items table created\n";
    
    // Create indexes
    echo "\nCreating indexes...\n";
    $conn->exec("CREATE INDEX idx_products_category ON products(category_id)");
    $conn->exec("CREATE INDEX idx_products_supplier ON products(supplier_id)");
    $conn->exec("CREATE INDEX idx_orders_user ON orders(user_id)");
    $conn->exec("CREATE INDEX idx_order_items_order ON order_items(order_id)");
    $conn->exec("CREATE INDEX idx_order_items_product ON order_items(product_id)");
    echo "✓ Indexes created\n";
    
    // Commit transaction
    $conn->commit();
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nYou can now register users and use the application.\n";
    
    echo json_encode([
        'success' => true,
        'message' => 'Database migration completed successfully'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
