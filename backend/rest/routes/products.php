<?php

/**
 * @OA\Get(
 *     path="/products",
 *     tags={"Products"},
 *     summary="Get all products",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Laptop"),
 *                     @OA\Property(property="description", type="string", example="High performance laptop"),
 *                     @OA\Property(property="price", type="number", format="float", example=999.99),
 *                     @OA\Property(property="quantity", type="integer", example=10),
 *                     @OA\Property(property="category_id", type="integer", example=2),
 *                     @OA\Property(property="supplier_id", type="integer", example=3)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Error message")
 *         )
 *     )
 * )
 */
Flight::route('GET /products', function() {
    try {
        $service = new ProductService();
        $products = $service->getAll();
        Flight::json(['success' => true, 'data' => $products], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Get product by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Laptop"),
 *                 @OA\Property(property="description", type="string", example="High performance laptop"),
 *                 @OA\Property(property="price", type="number", format="float", example=999.99),
 *                 @OA\Property(property="quantity", type="integer", example=10),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(property="supplier_id", type="integer", example=3)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Product not found")
 *         )
 *     )
 * )
 */
Flight::route('GET /products/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Product ID is required'], 400);
        return;
    }
    
    try {
        $service = new ProductService();
        $product = $service->getById($id);
        Flight::json(['success' => true, 'data' => $product], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 404);
    }
});

/**
 * @OA\Post(
 *     path="/products",
 *     tags={"Products"},
 *     summary="Create a new product",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description", "price", "quantity", "category_id", "supplier_id"},
 *             @OA\Property(property="name", type="string", example="Laptop"),
 *             @OA\Property(property="description", type="string", example="High performance laptop"),
 *             @OA\Property(property="price", type="number", format="float", example=999.99),
 *             @OA\Property(property="quantity", type="integer", example=10),
 *             @OA\Property(property="category_id", type="integer", example=2),
 *             @OA\Property(property="supplier_id", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Product created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Laptop"),
 *                 @OA\Property(property="description", type="string", example="High performance laptop"),
 *                 @OA\Property(property="price", type="number", format="float", example=999.99),
 *                 @OA\Property(property="quantity", type="integer", example=10),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(property="supplier_id", type="integer", example=3)
 *             ),
 *             @OA\Property(property="message", type="string", example="Product created successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Error message")
 *         )
 *     )
 * )
 */
Flight::route('POST /products', function() {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new ProductService();
        $product = $service->create($data);
        Flight::json(['success' => true, 'data' => $product, 'message' => 'Product created successfully'], 201);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Update a product",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description", "price", "quantity", "category_id", "supplier_id"},
 *             @OA\Property(property="name", type="string", example="Laptop"),
 *             @OA\Property(property="description", type="string", example="High performance laptop"),
 *             @OA\Property(property="price", type="number", format="float", example=999.99),
 *             @OA\Property(property="quantity", type="integer", example=10),
 *             @OA\Property(property="category_id", type="integer", example=2),
 *             @OA\Property(property="supplier_id", type="integer", example=3)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Laptop"),
 *                 @OA\Property(property="description", type="string", example="High performance laptop"),
 *                 @OA\Property(property="price", type="number", format="float", example=999.99),
 *                 @OA\Property(property="quantity", type="integer", example=10),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(property="supplier_id", type="integer", example=3)
 *             ),
 *             @OA\Property(property="message", type="string", example="Product updated successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Error message")
 *         )
 *     )
 * )
 */
Flight::route('PUT /products/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Product ID is required'], 400);
        return;
    }
    
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new ProductService();
        $product = $service->update($id, $data);
        Flight::json(['success' => true, 'data' => $product, 'message' => 'Product updated successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/products/{id}",
 *     tags={"Products"},
 *     summary="Delete a product",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Product deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Error message")
 *         )
 *     )
 * )
 */
Flight::route('DELETE /products/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Product ID is required'], 400);
        return;
    }
    
    try {
        $service = new ProductService();
        $service->delete($id);
        Flight::json(['success' => true, 'message' => 'Product deleted successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});
?>

