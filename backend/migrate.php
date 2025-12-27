<?php
/**
 * Database Migration Tool
 * 
 * This script handles the creation of database tables for the Inventory Manager.
 * It supports both MySQL and PostgreSQL.
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$expectedSecret = getenv('MIGRATION_SECRET') ?: 'migrate123';
$providedSecret = $_REQUEST['secret'] ?? '';
$action = $_REQUEST['action'] ?? '';

// Check if we should run the migration
$shouldRun = ($providedSecret === $expectedSecret) && ($action === 'migrate');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration Tool</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; color: #333; }
        h1 { border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .card { background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .log { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: monospace; white-space: pre-wrap; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        input[type="text"] { padding: 8px; width: 100%; max-width: 300px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Database Migration Tool</h1>

    <?php if (!$shouldRun): ?>
        <div class="card">
            <h2>⚠️ Warning</h2>
            <p>This tool will <strong>DROP ALL EXISTING TABLES</strong> and recreate them. All data will be lost.</p>
            <p>Please enter the migration secret to continue.</p>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="migrate">
                <label>
                    Migration Secret:<br>
                    <input type="text" name="secret" value="<?php echo htmlspecialchars($providedSecret); ?>" placeholder="Enter secret key">
                </label>
                <br>
                <button type="submit" class="btn btn-danger">Run Migration</button>
            </form>
            
            <?php if ($providedSecret && $providedSecret !== $expectedSecret): ?>
                <p class="error" style="margin-top: 10px;">❌ Incorrect secret key.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>Migration Log</h2>
            <div class="log">
<?php
    // Buffer output so we can capture it or display it in real-time
    // Note: In PHP buffering might prevent real-time output in some server configs, but it's better than nothing.
    
    require_once __DIR__ . '/config/config.php';

    try {
        echo "Connecting to database...\n";
        $conn = Database::connect();
        
        if (!$conn) {
            throw new Exception("Failed to connect to database (Database::connect returned null).");
        }
        
        $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        echo "Connected! Driver: <span class='success'>$driver</span>\n\n";
        
        // Start transaction
        $conn->beginTransaction();
        
        // Disable foreign key checks for MySQL
        if ($driver === 'mysql') {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        }

        // Drop existing tables
        echo "Dropping existing tables...\n";
        $tables = ['order_items', 'orders', 'products', 'categories', 'suppliers', 'users'];
        
        foreach ($tables as $table) {
            try {
                if ($driver === 'pgsql') {
                    $conn->exec("DROP TABLE IF EXISTS $table CASCADE");
                } else {
                    $conn->exec("DROP TABLE IF EXISTS $table");
                }
                echo "Dropped table: $table\n";
            } catch (Exception $e) {
                echo "Warning dropping $table: " . $e->getMessage() . "\n";
            }
        }
        echo "<span class='success'>✓ Tables dropped</span>\n\n";
        
        // Define types based on driver
        $autoIncrement = $driver === 'pgsql' ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $intType = 'INTEGER'; 
        
        // Create users table
        echo "Creating users table...\n";
        $conn->exec("
            CREATE TABLE users (
                user_id $autoIncrement,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<span class='success'>✓ Users table created</span>\n";
        
        // Create categories table
        echo "Creating categories table...\n";
        $conn->exec("
            CREATE TABLE categories (
                category_id $autoIncrement,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<span class='success'>✓ Categories table created</span>\n";
        
        // Create suppliers table
        echo "Creating suppliers table...\n";
        $conn->exec("
            CREATE TABLE suppliers (
                supplier_id $autoIncrement,
                name VARCHAR(100) NOT NULL,
                contact_person VARCHAR(100),
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<span class='success'>✓ Suppliers table created</span>\n";
        
        // Create products table
        echo "Creating products table...\n";
        $conn->exec("
            CREATE TABLE products (
                product_id $autoIncrement,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                price DECIMAL(10, 2) NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 0,
                category_id $intType REFERENCES categories(category_id) ON DELETE SET NULL,
                supplier_id $intType REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<span class='success'>✓ Products table created</span>\n";
        
        // Create orders table
        echo "Creating orders table...\n";
        $conn->exec("
            CREATE TABLE orders (
                order_id $autoIncrement,
                user_id $intType REFERENCES users(user_id) ON DELETE CASCADE,
                total_amount DECIMAL(10, 2) NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<span class='success'>✓ Orders table created</span>\n";
        
        // Create order_items table
        echo "Creating order_items table...\n";
        $conn->exec("
            CREATE TABLE order_items (
                order_item_id $autoIncrement,
                order_id $intType REFERENCES orders(order_id) ON DELETE CASCADE,
                product_id $intType REFERENCES products(product_id) ON DELETE CASCADE,
                quantity INTEGER NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<span class='success'>✓ Order_items table created</span>\n";
        
        // Create indexes
        echo "\nCreating indexes...\n";
        $conn->exec("CREATE INDEX idx_products_category ON products(category_id)");
        $conn->exec("CREATE INDEX idx_products_supplier ON products(supplier_id)");
        $conn->exec("CREATE INDEX idx_orders_user ON orders(user_id)");
        $conn->exec("CREATE INDEX idx_order_items_order ON order_items(order_id)");
        $conn->exec("CREATE INDEX idx_order_items_product ON order_items(product_id)");
        echo "<span class='success'>✓ Indexes created</span>\n";
        
        // Re-enable foreign key checks for MySQL
        if ($driver === 'mysql') {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        // Commit transaction
        $conn->commit();
        
        echo "\n<span class='success' style='font-size: 1.2em'>✅ MIGRATION COMPLETED SUCCESSFULLY!</span>\n";
        
    } catch (Exception $e) {
        if (isset($conn) && $conn) {
            $conn->rollBack();
        }
        echo "\n<span class='error'>❌ Migration failed: " . $e->getMessage() . "</span>\n";
        echo "Stack trace:\n" . $e->getTraceAsString();
    }
?>
            </div>
            <p><a href="migrate.php" class="btn">Back</a></p>
        </div>
    <?php endif; ?>
</body>
</html>
