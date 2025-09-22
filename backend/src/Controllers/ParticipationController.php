<?php

namespace EcoRide\Controllers;

use EcoRide\Services\ParticipationService;
use EcoRide\Middleware\AuthMiddleware;

class ParticipationController {
    private $participationService;
    
    public function __construct() {
        $this->participationService = new ParticipationService();
    }
    
    public function create(array $params): array {
        $user = AuthMiddleware::authenticate();
        $tripId = $params['tripId'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$tripId) {
            http_response_code(400);
            return ['error' => 'Trip ID is required'];
        }
        
        try {
            $data['trip_id'] = $tripId;
            $data['passenger_id'] = $user['id'];
            return $this->participationService->createParticipation($data);
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function updateStatus(array $params): array {
        $user = AuthMiddleware::authenticate();
        $participationId = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$participationId) {
            http_response_code(400);
            return ['error' => 'Participation ID is required'];
        }
        
        if (!isset($data['status'])) {
            http_response_code(400);
            return ['error' => 'Status is required'];
        }
        
        try {
            return $this->participationService->updateParticipationStatus($participationId, $data['status'], $user['id']);
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getById(array $params): array {
        $user = AuthMiddleware::authenticate();
        $participationId = $params['id'] ?? null;
        
        if (!$participationId) {
            http_response_code(400);
            return ['error' => 'Participation ID is required'];
        }
        
        try {
            $participation = $this->participationService->getParticipationById($participationId, $user['id']);
            if (!$participation) {
                http_response_code(404);
                return ['error' => 'Participation not found'];
            }
            return $participation;
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}