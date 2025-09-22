<?php

require_once __DIR__ . '/../vendor/autoload.php';

use EcoRide\Core\Router;
use EcoRide\Core\Database;
use EcoRide\Middleware\CorsMiddleware;
use EcoRide\Controllers\AuthController;
use EcoRide\Controllers\UserController;
use EcoRide\Controllers\TripController;
use EcoRide\Controllers\ParticipationController;
use EcoRide\Controllers\ReviewController;
use EcoRide\Controllers\VehicleController;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Enable CORS
CorsMiddleware::handle();

// Initialize database
Database::getInstance();

// Initialize router
$router = new Router();

// Auth routes
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/verify-email', [AuthController::class, 'verifyEmail']);
$router->post('/api/auth/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/api/auth/reset-password', [AuthController::class, 'resetPassword']);

// User routes
$router->get('/api/user/profile', [UserController::class, 'getProfile']);
$router->put('/api/user/profile', [UserController::class, 'updateProfile']);
$router->get('/api/user/credits', [UserController::class, 'getCredits']);

// Vehicle routes
$router->get('/api/vehicles', [VehicleController::class, 'getUserVehicles']);
$router->post('/api/vehicles', [VehicleController::class, 'create']);
$router->put('/api/vehicles/{id}', [VehicleController::class, 'update']);
$router->delete('/api/vehicles/{id}', [VehicleController::class, 'delete']);

// Trip routes
$router->get('/api/trips', [TripController::class, 'search']);
$router->post('/api/trips', [TripController::class, 'create']);
$router->get('/api/trips/{id}', [TripController::class, 'getById']);
$router->put('/api/trips/{id}', [TripController::class, 'update']);
$router->delete('/api/trips/{id}', [TripController::class, 'cancel']);
$router->get('/api/trips/user/driver', [TripController::class, 'getDriverTrips']);
$router->get('/api/trips/user/passenger', [TripController::class, 'getPassengerTrips']);

// Participation routes
$router->post('/api/trips/{tripId}/participate', [ParticipationController::class, 'create']);
$router->put('/api/participations/{id}/status', [ParticipationController::class, 'updateStatus']);
$router->get('/api/participations/{id}', [ParticipationController::class, 'getById']);

// Review routes
$router->post('/api/trips/{tripId}/reviews', [ReviewController::class, 'create']);
$router->get('/api/users/{userId}/reviews', [ReviewController::class, 'getUserReviews']);

// Health check
$router->get('/api/health', function() {
    return ['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')];
});

// Handle the request
$router->handleRequest();