<?php

namespace EcoRide\Controllers;

use EcoRide\Services\ReviewService;
use EcoRide\Middleware\AuthMiddleware;

class ReviewController {
    private $reviewService;
    
    public function __construct() {
        $this->reviewService = new ReviewService();
    }
    
    public function create(array $params): array {
        $user = AuthMiddleware::authenticate();
        $tripId = $params['tripId'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$tripId) {
            http_response_code(400);
            return ['error' => 'Trip ID is required'];
        }
        
        $required = ['reviewed_id', 'rating', 'type'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                return ['error' => "Field '$field' is required"];
            }
        }
        
        try {
            $data['trip_id'] = $tripId;
            $data['reviewer_id'] = $user['id'];
            return $this->reviewService->createReview($data);
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getUserReviews(array $params): array {
        $userId = $params['userId'] ?? null;
        
        if (!$userId) {
            http_response_code(400);
            return ['error' => 'User ID is required'];
        }
        
        try {
            return $this->reviewService->getUserReviews($userId);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}