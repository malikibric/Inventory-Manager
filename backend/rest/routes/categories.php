<?php

/**
 * @OA\Get(
 *     path="/categories",
 *     tags={"Categories"},
 *     summary="Get all categories",
 *     description="Retrieve a list of all categories from the database",
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
 *                     @OA\Property(property="name", type="string", example="Electronics"),
 *                     @OA\Property(property="description", type="string", example="Electronic devices and accessories")
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
Flight::route('GET /categories', function() {
    try {
        $service = new CategoryService();
        $categories = $service->getAll();
        Flight::json(['success' => true, 'data' => $categories], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/categories/{id}",
 *     tags={"Categories"},
 *     summary="Get category by ID",
 *     description="Retrieve a single category by its ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Category ID",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Electronics"),
 *                 @OA\Property(property="description", type="string", example="Electronic devices")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Category not found")
 *         )
 *     )
 * )
 */
Flight::route('GET /categories/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Category ID is required'], 400);
        return;
    }
    
    try {
        $service = new CategoryService();
        $category = $service->getById($id);
        Flight::json(['success' => true, 'data' => $category], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 404);
    }
});

/**
 * @OA\Post(
 *     path="/categories",
 *     tags={"Categories"},
 *     summary="Create a new category",
 *     description="Add a new category to the database",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Category data",
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Books"),
 *             @OA\Property(property="description", type="string", example="Books and literature")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Category created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=5),
 *                 @OA\Property(property="name", type="string", example="Books"),
 *                 @OA\Property(property="description", type="string", example="Books and literature")
 *             ),
 *             @OA\Property(property="message", type="string", example="Category created successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Validation error")
 *         )
 *     )
 * )
 */
Flight::route('POST /categories', function() {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new CategoryService();
        $category = $service->create($data);
        Flight::json(['success' => true, 'data' => $category, 'message' => 'Category created successfully'], 201);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/categories/{id}",
 *     tags={"Categories"},
 *     summary="Update a category",
 *     description="Update an existing category by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Category ID",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Updated category data",
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Category"),
 *             @OA\Property(property="description", type="string", example="Updated description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Updated Category"),
 *                 @OA\Property(property="description", type="string", example="Updated description")
 *             ),
 *             @OA\Property(property="message", type="string", example="Category updated successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or category not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Error message")
 *         )
 *     )
 * )
 */
Flight::route('PUT /categories/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Category ID is required'], 400);
        return;
    }
    
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new CategoryService();
        $category = $service->update($id, $data);
        Flight::json(['success' => true, 'data' => $category, 'message' => 'Category updated successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/categories/{id}",
 *     tags={"Categories"},
 *     summary="Delete a category",
 *     description="Delete a category by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Category ID",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Category deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Category not found or error occurred",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Category not found")
 *         )
 *     )
 * )
 */
Flight::route('DELETE /categories/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Category ID is required'], 400);
        return;
    }
    
    try {
        $service = new CategoryService();
        $service->delete($id);
        Flight::json(['success' => true, 'message' => 'Category deleted successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});
?>