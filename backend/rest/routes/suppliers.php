<?php

/**
 * @OA\Get(
 *     path="/suppliers",
 *     tags={"Suppliers"},
 *     summary="Get all suppliers",
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
 *                     @OA\Property(property="name", type="string", example="Acme Corp"),
 *                     @OA\Property(property="contact_name", type="string", example="John Doe"),
 *                     @OA\Property(property="contact_email", type="string", example="john@acme.com"),
 *                     @OA\Property(property="contact_phone", type="string", example="+1234567890")
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
Flight::route('GET /suppliers', function() {
    try {
        $service = new SupplierService();
        $suppliers = $service->getAll();
        Flight::json(['success' => true, 'data' => $suppliers], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/suppliers/{id}",
 *     tags={"Suppliers"},
 *     summary="Get supplier by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Supplier ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Supplier found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Acme Corp"),
 *                 @OA\Property(property="contact_name", type="string", example="John Doe"),
 *                 @OA\Property(property="contact_email", type="string", example="john@acme.com"),
 *                 @OA\Property(property="contact_phone", type="string", example="+1234567890")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Supplier not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="error", type="string", example="Supplier not found")
 *         )
 *     )
 * )
 */
Flight::route('GET /suppliers/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Supplier ID is required'], 400);
        return;
    }
    
    try {
        $service = new SupplierService();
        $supplier = $service->getById($id);
        Flight::json(['success' => true, 'data' => $supplier], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 404);
    }
});

/**
 * @OA\Post(
 *     path="/suppliers",
 *     tags={"Suppliers"},
 *     summary="Create a new supplier",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "contact_name", "contact_email", "contact_phone"},
 *             @OA\Property(property="name", type="string", example="Acme Corp"),
 *             @OA\Property(property="contact_name", type="string", example="John Doe"),
 *             @OA\Property(property="contact_email", type="string", example="john@acme.com"),
 *             @OA\Property(property="contact_phone", type="string", example="+1234567890")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Supplier created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Acme Corp"),
 *                 @OA\Property(property="contact_name", type="string", example="John Doe"),
 *                 @OA\Property(property="contact_email", type="string", example="john@acme.com"),
 *                 @OA\Property(property="contact_phone", type="string", example="+1234567890")
 *             ),
 *             @OA\Property(property="message", type="string", example="Supplier created successfully")
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
Flight::route('POST /suppliers', function() {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new SupplierService();
        $supplier = $service->create($data);
        Flight::json(['success' => true, 'data' => $supplier, 'message' => 'Supplier created successfully'], 201);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/suppliers/{id}",
 *     tags={"Suppliers"},
 *     summary="Update a supplier",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Supplier ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "contact_name", "contact_email", "contact_phone"},
 *             @OA\Property(property="name", type="string", example="Acme Corp"),
 *             @OA\Property(property="contact_name", type="string", example="John Doe"),
 *             @OA\Property(property="contact_email", type="string", example="john@acme.com"),
 *             @OA\Property(property="contact_phone", type="string", example="+1234567890")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Supplier updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Acme Corp"),
 *                 @OA\Property(property="contact_name", type="string", example="John Doe"),
 *                 @OA\Property(property="contact_email", type="string", example="john@acme.com"),
 *                 @OA\Property(property="contact_phone", type="string", example="+1234567890")
 *             ),
 *             @OA\Property(property="message", type="string", example="Supplier updated successfully")
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
Flight::route('PUT /suppliers/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Supplier ID is required'], 400);
        return;
    }
    
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $service = new SupplierService();
        $supplier = $service->update($id, $data);
        Flight::json(['success' => true, 'data' => $supplier, 'message' => 'Supplier updated successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/suppliers/{id}",
 *     tags={"Suppliers"},
 *     summary="Delete a supplier",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Supplier ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Supplier deleted",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Supplier deleted successfully")
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
Flight::route('DELETE /suppliers/@id', function($id) {
    if (empty($id)) {
        Flight::json(['success' => false, 'error' => 'Supplier ID is required'], 400);
        return;
    }
    
    try {
        $service = new SupplierService();
        $service->delete($id);
        Flight::json(['success' => true, 'message' => 'Supplier deleted successfully'], 200);
    } catch (Exception $e) {
        Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
    }
});
?>

