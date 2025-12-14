<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Clinic Management API",
 *     description="API documentation for Clinic Management System with Sanctum authentication",
 *     @OA\Contact(
 *         email="admin@clinic.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local development server"
 * )
 *
 * @OA\Server(
 *     url="https://ecv1-api.testingelmo.com/api/v1",
 *     description="Testing server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format: Bearer <token>"
 * )
 */
abstract class Controller
{
    //
}
