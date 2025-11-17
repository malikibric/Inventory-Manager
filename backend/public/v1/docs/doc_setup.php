<?php
/**
 * @OA\Info(
 *     title="Inventory Manager API",
 *     version="1.0.0",
 *     description="API documentation for the Inventory Managment",
 *     @OA\Contact(
 *         email="malik.ibric@stu.ibu.edu.ba",
 *         name="Inventory Manager Team"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost/inventorymanager/backend",
 *     description="Local development server"
 * )
 *
 * @OA\Server(
 *     url="https://lobster-app-czvm2.ondigitalocean.app/backend",
 *     description="Production server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="Authentication"
 * )
 */
