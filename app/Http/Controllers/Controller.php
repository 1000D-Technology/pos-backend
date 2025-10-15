<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="POS System Backend API",
 *      description="API documentation for the Point of Sale system, including Authentication and Protected Resources.",
 *      @OA\Contact(
 *          email="1000dtechnology.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Development API Host"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Main API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Sanctum token for API authentication (Prefix with 'Bearer ')"
 * )
 */
abstract class Controller
{
    //
}
