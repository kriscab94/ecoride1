<?php

namespace EcoRide\Controllers;

use EcoRide\Services\VehicleService;
use EcoRide\Middleware\AuthMiddleware;

class VehicleController {
    private $vehicleService;
    
    public function __construct() {
        $this->vehicleService = new VehicleService();
    }
    
    public function getUserVehicles(): array {
        $user = AuthMiddleware::authenticate();
        
        try {
            return $this->vehicleService->getUserVehicles($user['id']);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function create(): array {
        $user = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            return ['error' => 'Invalid JSON data'];
        }
        
        $required = ['make', 'model', 'year', 'seats', 'fuel_type'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                return ['error' => "Field '$field' is required"];
            }
        }
        
        try {
            $data['user_id'] = $user['id'];
            return $this->vehicleService->createVehicle($data);
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function update(array $params): array {
        $user = AuthMiddleware::authenticate();
        $vehicleId = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$vehicleId) {
            http_response_code(400);
            return ['error' => 'Vehicle ID is required'];
        }
        
        try {
            return $this->vehicleService->updateVehicle($vehicleId, $data, $user['id']);
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function delete(array $params): array {
        $user = AuthMiddleware::authenticate();
        $vehicleId = $params['id'] ?? null;
        
        if (!$vehicleId) {
            http_response_code(400);
            return ['error' => 'Vehicle ID is required'];
        }
        
        try {
            $this->vehicleService->deleteVehicle($vehicleId, $user['id']);
            return ['message' => 'Vehicle deleted successfully'];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
}