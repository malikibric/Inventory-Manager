<?php

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"Users"},
 *     summary="Get all users",
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
 *                     @OA\Property(property="username", type="string", example="johndoe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com"),
 *                     @OA\Property(property="role", type="string", example="admin")
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
Flight::route('GET /users', function() {
    try {
        $service = new UserService();
        $users = $service->getAll();
        Flight::json(['success' => true, 'data' => $users], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/users/{id}",
 *     tags={"Users"},
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="username", type="string", example="johndoe"),
 *                 @OA\Property(property="email", type="string", example="john@example.com"),
 *                 @OA\Property(property="role", type="string", example="admin")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="User not found")
 *         )
 *     )
 * )
 */
Flight::route('GET /users/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'User ID is required'], 400);
        return;
    }
    
    try {
        $service = new UserService();
        $user = $service->getById($id);
        Flight::json(['success' => true, 'data' => $user], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 404);
    }
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"Users"},
 *     summary="Create a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "email", "password", "role"},
 *             @OA\Property(property="username", type="string", example="johndoe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="secret123"),
 *             @OA\Property(property="role", type="string", example="admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="username", type="string", example="johndoe"),
 *                 @OA\Property(property="email", type="string", example="john@example.com"),
 *                 @OA\Property(property="role", type="string", example="admin")
 *             ),
 *             @OA\Property(property="message", type="string", example="User created successfully")
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
Flight::route('POST /users', function() {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        // Remove user_id and id if present, let MySQL auto-increment
        unset($data['user_id'], $data['id']);
        $service = new UserService();
        $user = $service->create($data);
        Flight::json(['success' => true, 'data' => $user, 'message' => 'User created successfully'], 201);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     tags={"Users"},
 *     summary="Update a user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "email", "role"},
 *             @OA\Property(property="username", type="string", example="johndoe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="role", type="string", example="admin")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="username", type="string", example="johndoe"),
 *                 @OA\Property(property="email", type="string", example="john@example.com"),
 *                 @OA\Property(property="role", type="string", example="admin")
 *             ),
 *             @OA\Property(property="message", type="string", example="User updated successfully")
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
Flight::route('PUT /users/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'User ID is required'], 400);
        return;
    }
    
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new UserService();
        $user = $service->update($id, $data);
        Flight::json(['success' => true, 'data' => $user, 'message' => 'User updated successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/users/{id}",
 *     tags={"Users"},
 *     summary="Delete a user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User deleted successfully")
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
Flight::route('DELETE /users/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'User ID is required'], 400);
        return;
    }
    
    try {
        $service = new UserService();
        $service->delete($id);
        Flight::json(['success' => true, 'message' => 'User deleted successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});
?>

