<?php

namespace EcoRide\Controllers;

use EcoRide\Services\UserService;
use EcoRide\Middleware\AuthMiddleware;

class UserController {
    private $userService;
    
    public function __construct() {
        $this->userService = new UserService();
    }
    
    public function getProfile(): array {
        $user = AuthMiddleware::authenticate();
        
        try {
            return $this->userService->getUserProfile($user['id']);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function updateProfile(): array {
        $user = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            return ['error' => 'Invalid JSON data'];
        }
        
        try {
            return $this->userService->updateUserProfile($user['id'], $data);
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getCredits(): array {
        $user = AuthMiddleware::authenticate();
        
        try {
            return $this->userService->getUserCredits($user['id']);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}