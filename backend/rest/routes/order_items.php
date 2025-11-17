<?php

/**
 * @OA\Get(
 *     path="/order-items",
 *     tags={"Order Items"},
 *     summary="Get all order items",
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
 *                     @OA\Property(property="order_id", type="integer", example=1),
 *                     @OA\Property(property="product_id", type="integer", example=2),
 *                     @OA\Property(property="quantity", type="integer", example=5),
 *                     @OA\Property(property="price", type="number", format="float", example=19.99)
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
Flight::route('GET /order-items', function() {
    try {
        $service = new OrderItemService();
        $orderItems = $service->getAll();
        Flight::json(['success' => true, 'data' => $orderItems], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/order-items/{id}",
 *     tags={"Order Items"},
 *     summary="Get order item by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order Item ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order item found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="order_id", type="integer", example=1),
 *                 @OA\Property(property="product_id", type="integer", example=2),
 *                 @OA\Property(property="quantity", type="integer", example=5),
 *                 @OA\Property(property="price", type="number", format="float", example=19.99)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order item not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Order item not found")
 *         )
 *     )
 * )
 */
Flight::route('GET /order-items/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Order item ID is required'], 400);
        return;
    }
    
    try {
        $service = new OrderItemService();
        $orderItem = $service->getById($id);
        Flight::json(['success' => true, 'data' => $orderItem], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 404);
    }
});

/**
 * @OA\Post(
 *     path="/order-items",
 *     tags={"Order Items"},
 *     summary="Create a new order item",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"order_id", "product_id", "quantity", "price"},
 *             @OA\Property(property="order_id", type="integer", example=1),
 *             @OA\Property(property="product_id", type="integer", example=2),
 *             @OA\Property(property="quantity", type="integer", example=5),
 *             @OA\Property(property="price", type="number", format="float", example=19.99)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Order item created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="order_id", type="integer", example=1),
 *                 @OA\Property(property="product_id", type="integer", example=2),
 *                 @OA\Property(property="quantity", type="integer", example=5),
 *                 @OA\Property(property="price", type="number", format="float", example=19.99)
 *             ),
 *             @OA\Property(property="message", type="string", example="Order item created successfully")
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
Flight::route('POST /order-items', function() {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new OrderItemService();
        $orderItem = $service->create($data);
        Flight::json(['success' => true, 'data' => $orderItem, 'message' => 'Order item created successfully'], 201);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/order-items/{id}",
 *     tags={"Order Items"},
 *     summary="Update an order item",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order Item ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"order_id", "product_id", "quantity", "price"},
 *             @OA\Property(property="order_id", type="integer", example=1),
 *             @OA\Property(property="product_id", type="integer", example=2),
 *             @OA\Property(property="quantity", type="integer", example=5),
 *             @OA\Property(property="price", type="number", format="float", example=19.99)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order item updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="order_id", type="integer", example=1),
 *                 @OA\Property(property="product_id", type="integer", example=2),
 *                 @OA\Property(property="quantity", type="integer", example=5),
 *                 @OA\Property(property="price", type="number", format="float", example=19.99)
 *             ),
 *             @OA\Property(property="message", type="string", example="Order item updated successfully")
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
Flight::route('PUT /order-items/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Order item ID is required'], 400);
        return;
    }
    
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new OrderItemService();
        $orderItem = $service->update($id, $data);
        Flight::json(['success' => true, 'data' => $orderItem, 'message' => 'Order item updated successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/order-items/{id}",
 *     tags={"Order Items"},
 *     summary="Delete an order item",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order Item ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order item deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Order item deleted successfully")
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
Flight::route('DELETE /order-items/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Order item ID is required'], 400);
        return;
    }
    
    try {
        $service = new OrderItemService();
        $service->delete($id);
        Flight::json(['success' => true, 'message' => 'Order item deleted successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});
?>

