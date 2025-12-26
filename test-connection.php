#!/usr/bin/env php
<?php
/**
 * Test Database Connection
 * Run: php test-connection.php
 */

// Load environment variables from .env file if exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

require_once __DIR__ . '/backend/config/config.php';

echo "========================================\n";
echo "Database Connection Test\n";
echo "========================================\n\n";

// Parse DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
    echo "✓ DATABASE_URL found\n";
    $parsed = parse_url($databaseUrl);
    echo "  Host: " . ($parsed['host'] ?? 'N/A') . "\n";
    echo "  Port: " . ($parsed['port'] ?? 'default') . "\n";
    echo "  Database: " . ltrim($parsed['path'] ?? '', '/') . "\n";
    echo "  User: " . ($parsed['user'] ?? 'N/A') . "\n";
    echo "  Scheme: " . ($parsed['scheme'] ?? 'N/A') . "\n\n";
} else {
    echo "✗ DATABASE_URL not found\n\n";
}

// Test connection
echo "Testing connection...\n";
try {
    $db = Database::connect();
    echo "✓ Connection successful!\n\n";
    
    // Test query
    echo "Testing query...\n";
    $stmt = $db->query("SELECT current_database(), current_user, version()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✓ Query successful!\n";
    echo "  Current Database: " . $result['current_database'] . "\n";
    echo "  Current User: " . $result['current_user'] . "\n";
    echo "  PostgreSQL Version: " . substr($result['version'], 0, 50) . "...\n\n";
    
    // Check if tables exist
    echo "Checking tables...\n";
    $stmt = $db->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✓ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - " . $table . "\n";
        }
    } else {
        echo "⚠ No tables found. Run database_postgresql.sql to create schema.\n";
    }
    
    echo "\n========================================\n";
    echo "✓ All tests passed!\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "========================================\n";
    echo "✗ Tests failed!\n";
    echo "========================================\n";
    exit(1);
}
?>
