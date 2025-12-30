<?php
/**
 * Health Check Endpoint
 * Used by Render to verify the service is running
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => getenv('APP_ENV') ?: 'development',
    'version' => '1.0.0',
    'checks' => []
];

// Check database connection
try {
    require_once __DIR__ . '/config/config.php';
    $db = Database::connect();
    $health['checks']['database'] = 'connected';
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = 'failed: ' . $e->getMessage();
    http_response_code(503);
}

// Check PHP version
$health['checks']['php_version'] = PHP_VERSION;

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'pdo_pgsql', 'json'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    $health['status'] = 'degraded';
    $health['checks']['extensions'] = 'missing: ' . implode(', ', $missingExtensions);
} else {
    $health['checks']['extensions'] = 'ok';
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>
