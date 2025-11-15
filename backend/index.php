<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/vendor/mikecao/flight/Flight.php';
}

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/config/',
        __DIR__ . '/dao/',
        __DIR__ . '/services/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/rest/routes/categories.php';
require_once __DIR__ . '/rest/routes/products.php';
require_once __DIR__ . '/rest/routes/orders.php';
require_once __DIR__ . '/rest/routes/order_items.php';
require_once __DIR__ . '/rest/routes/suppliers.php';
require_once __DIR__ . '/rest/routes/users.php';


Flight::route('GET /', function() {
    Flight::json([
        'success' => true,
        'message' => 'Inventory Manager API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /categories' => 'List all categories',
            'GET /categories/{id}' => 'Get category by ID',
            'POST /categories' => 'Create category',
            'PUT /categories/{id}' => 'Update category',
            'DELETE /categories/{id}' => 'Delete category',
            'GET /products' => 'List all products',
            'GET /products/{id}' => 'Get product by ID',
            'POST /products' => 'Create product',
            'PUT /products/{id}' => 'Update product',
            'DELETE /products/{id}' => 'Delete product',
            'GET /orders' => 'List all orders',
            'GET /orders/{id}' => 'Get order by ID',
            'POST /orders' => 'Create order',
            'PUT /orders/{id}' => 'Update order',
            'DELETE /orders/{id}' => 'Delete order',
            'GET /order-items' => 'List all order items',
            'GET /order-items/{id}' => 'Get order item by ID',
            'POST /order-items' => 'Create order item',
            'PUT /order-items/{id}' => 'Update order item',
            'DELETE /order-items/{id}' => 'Delete order item',
            'GET /suppliers' => 'List all suppliers',
            'GET /suppliers/{id}' => 'Get supplier by ID',
            'POST /suppliers' => 'Create supplier',
            'PUT /suppliers/{id}' => 'Update supplier',
            'DELETE /suppliers/{id}' => 'Delete supplier',
            'GET /users' => 'List all users',
            'GET /users/{id}' => 'Get user by ID',
            'POST /users' => 'Create user',
            'PUT /users/{id}' => 'Update user',
            'DELETE /users/{id}' => 'Delete user',
        ]
    ], 200);
});

Flight::map('error', function(Exception $ex) {
    Flight::json([
        'success' => false,
        'error' => $ex->getMessage()
    ], 500);
});

Flight::map('notFound', function() {
    Flight::json([
        'success' => false,
        'error' => 'Endpoint not found'
    ], 404);
});

Flight::start();
?>

