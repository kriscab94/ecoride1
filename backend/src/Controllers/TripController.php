<?php

namespace EcoRide\Controllers;

use EcoRide\Services\TripService;
use EcoRide\Middleware\AuthMiddleware;

class TripController {
    private $tripService;
    
    public function __construct() {
        $this->tripService = new TripService();
    }
    
    public function search(): array {
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        $date = $_GET['date'] ?? '';
        $seats = $_GET['seats'] ?? 1;
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;
        
        try {
            return $this->tripService->searchTrips($from, $to, $date, $seats, $page, $limit);
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
        
        $required = ['vehicle_id', 'departure_city', 'departure_address', 'arrival_city', 'arrival_address', 'departure_time', 'available_seats', 'price_per_seat'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                return ['error' => "Field '$field' is required"];
            }
        }
        
        try {
            $data['driver_id'] = $user['id'];
            $trip = $this->tripService->createTrip($data);
            return $trip;
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getById(array $params): array {
        $tripId = $params['id'] ?? null;
        
        if (!$tripId) {
            http_response_code(400);
            return ['error' => 'Trip ID is required'];
        }
        
        try {
            $trip = $this->tripService->getTripById($tripId);
            if (!$trip) {
                http_response_code(404);
                return ['error' => 'Trip not found'];
            }
            return $trip;
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function update(array $params): array {
        $user = AuthMiddleware::authenticate();
        $tripId = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$tripId) {
            http_response_code(400);
            return ['error' => 'Trip ID is required'];
        }
        
        try {
            $trip = $this->tripService->updateTrip($tripId, $data, $user['id']);
            return $trip;
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function cancel(array $params): array {
        $user = AuthMiddleware::authenticate();
        $tripId = $params['id'] ?? null;
        
        if (!$tripId) {
            http_response_code(400);
            return ['error' => 'Trip ID is required'];
        }
        
        try {
            $this->tripService->cancelTrip($tripId, $user['id']);
            return ['message' => 'Trip cancelled successfully'];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getDriverTrips(): array {
        $user = AuthMiddleware::authenticate();
        
        try {
            return $this->tripService->getUserTripsAsDriver($user['id']);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPassengerTrips(): array {
        $user = AuthMiddleware::authenticate();
        
        try {
            return $this->tripService->getUserTripsAsPassenger($user['id']);
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
}