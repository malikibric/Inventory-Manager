<?php

/**
 * @OA\Get(
 *     path="/orders",
 *     tags={"Orders"},
 *     summary="Get all orders",
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
 *                     @OA\Property(property="user_id", type="integer", example=2),
 *                     @OA\Property(property="order_date", type="string", format="date", example="2025-11-13"),
 *                     @OA\Property(property="status", type="string", example="pending")
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
Flight::route('GET /orders', function() {
    try {
        $service = new OrderService();
        $orders = $service->getAll();
        Flight::json(['success' => true, 'data' => $orders], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/orders/{id}",
 *     tags={"Orders"},
 *     summary="Get order by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=2),
 *                 @OA\Property(property="order_date", type="string", format="date", example="2025-11-13"),
 *                 @OA\Property(property="status", type="string", example="pending")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Order not found")
 *         )
 *     )
 * )
 */
Flight::route('GET /orders/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Order ID is required'], 400);
        return;
    }
    
    try {
        $service = new OrderService();
        $order = $service->getById($id);
        Flight::json(['success' => true, 'data' => $order], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 404);
    }
});

/**
 * @OA\Post(
 *     path="/orders",
 *     tags={"Orders"},
 *     summary="Create a new order",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "order_date", "status"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="order_date", type="string", format="date", example="2025-11-13"),
 *             @OA\Property(property="status", type="string", example="pending")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Order created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=2),
 *                 @OA\Property(property="order_date", type="string", format="date", example="2025-11-13"),
 *                 @OA\Property(property="status", type="string", example="pending")
 *             ),
 *             @OA\Property(property="message", type="string", example="Order created successfully")
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
Flight::route('POST /orders', function() {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new OrderService();
        $order = $service->create($data);
        Flight::json(['success' => true, 'data' => $order, 'message' => 'Order created successfully'], 201);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/orders/{id}",
 *     tags={"Orders"},
 *     summary="Update an order",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "order_date", "status"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="order_date", type="string", format="date", example="2025-11-13"),
 *             @OA\Property(property="status", type="string", example="completed")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=2),
 *                 @OA\Property(property="order_date", type="string", format="date", example="2025-11-13"),
 *                 @OA\Property(property="status", type="string", example="completed")
 *             ),
 *             @OA\Property(property="message", type="string", example="Order updated successfully")
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
Flight::route('PUT /orders/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Order ID is required'], 400);
        return;
    }
    
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new OrderService();
        $order = $service->update($id, $data);
        Flight::json(['success' => true, 'data' => $order, 'message' => 'Order updated successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/orders/{id}",
 *     tags={"Orders"},
 *     summary="Delete an order",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Order deleted successfully")
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
Flight::route('DELETE /orders/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Order ID is required'], 400);
        return;
    }
    
    try {
        $service = new OrderService();
        $service->delete($id);
        Flight::json(['success' => true, 'message' => 'Order deleted successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});
?>

