<?php
class Database {
    private static $connection = null;

    /**
     * Get database connection using environment variables
     * Supports both DATABASE_URL (Render/Heroku format) and individual variables
     */
    public static function connect() {
        if (self::$connection === null) {
            try {
                // Check if DATABASE_URL is set (Render, Heroku, etc.)
                $databaseUrl = getenv('DATABASE_URL');
                
                if ($databaseUrl) {
                    // Parse DATABASE_URL
                    $dbInfo = parse_url($databaseUrl);
                    
                    $host = $dbInfo['host'] ?? 'localhost';
                    $dbName = ltrim($dbInfo['path'] ?? '', '/');
                    $username = $dbInfo['user'] ?? 'root';
                    $password = $dbInfo['pass'] ?? '';
                    
                    // Determine if it's PostgreSQL or MySQL
                    $scheme = $dbInfo['scheme'] ?? 'mysql';
                    
                    if ($scheme === 'postgres' || $scheme === 'postgresql') {
                        // PostgreSQL connection
                        $port = $dbInfo['port'] ?? 5432;
                        $dsn = "pgsql:host=$host;port=$port;dbname=$dbName";
                    } else {
                        // MySQL connection
                        $port = $dbInfo['port'] ?? 3306;
                        $dsn = "mysql:host=$host;port=$port;dbname=$dbName";
                    }
                } else {
                    // Fall back to individual environment variables or defaults
                    $host = getenv('DB_HOST') ?: 'localhost';
                    $port = getenv('DB_PORT') ?: 3306;
                    $dbName = getenv('DB_NAME') ?: 'shop_db';
                    $username = getenv('DB_USER') ?: 'root';
                    $password = getenv('DB_PASSWORD') ?: '';
                    $dbType = getenv('DB_TYPE') ?: 'mysql';
                    
                    if ($dbType === 'pgsql' || $dbType === 'postgres' || $dbType === 'postgresql') {
                        $dsn = "pgsql:host=$host;port=$port;dbname=$dbName";
                    } else {
                        $dsn = "mysql:host=$host;port=$port;dbname=$dbName";
                    }
                }
                
                self::$connection = new PDO(
                    $dsn,
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                error_log("Database connected successfully to: " . $host);
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                
                // In production, don't expose database details
                if (getenv('APP_ENV') === 'production') {
                    die(json_encode([
                        'success' => false,
                        'error' => 'Database connection failed. Please contact support.'
                    ]));
                } else {
                    die(json_encode([
                        'success' => false,
                        'error' => 'Connection failed: ' . $e->getMessage()
                    ]));
                }
            }
        }
        return self::$connection;
    }
    
    /**
     * Close database connection
     */
    public static function disconnect() {
        self::$connection = null;
    }
}
?>
