<?php

namespace EcoRide\Services;

use EcoRide\Core\Database;

class ParticipationService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createParticipation(array $data): array {
        // Check if trip exists and is active
        $stmt = $this->db->prepare('
            SELECT id, driver_id, available_seats, price_per_seat, departure_time 
            FROM trips 
            WHERE id = ? AND status = "active" AND departure_time > NOW()
        ');
        $stmt->execute([$data['trip_id']]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            throw new \Exception('Trip not found or no longer available');
        }
        
        // Check if user is not the driver
        if ($trip['driver_id'] == $data['passenger_id']) {
            throw new \Exception('Cannot participate in your own trip');
        }
        
        // Check if user already participating
        $stmt = $this->db->prepare('
            SELECT id FROM participations 
            WHERE trip_id = ? AND passenger_id = ? AND status != "cancelled"
        ');
        $stmt->execute([$data['trip_id'], $data['passenger_id']]);
        if ($stmt->fetch()) {
            throw new \Exception('Already participating in this trip');
        }
        
        $seatsRequested = $data['seats_reserved'] ?? 1;
        
        // Check available seats
        $stmt = $this->db->prepare('
            SELECT COALESCE(SUM(seats_reserved), 0) as reserved_seats 
            FROM participations 
            WHERE trip_id = ? AND status IN ("pending", "confirmed")
        ');
        $stmt->execute([$data['trip_id']]);
        $reservedSeats = $stmt->fetchColumn();
        
        if ($reservedSeats + $seatsRequested > $trip['available_seats']) {
            throw new \Exception('Not enough seats available');
        }
        
        $totalPrice = $trip['price_per_seat'] * $seatsRequested;
        
        $stmt = $this->db->prepare('
            INSERT INTO participations (
                trip_id, passenger_id, seats_reserved, pickup_address, pickup_lat, pickup_lng,
                dropoff_address, dropoff_lat, dropoff_lng, total_price
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['trip_id'],
            $data['passenger_id'],
            $seatsRequested,
            $data['pickup_address'] ?? null,
            $data['pickup_lat'] ?? null,
            $data['pickup_lng'] ?? null,
            $data['dropoff_address'] ?? null,
            $data['dropoff_lat'] ?? null,
            $data['dropoff_lng'] ?? null,
            $totalPrice
        ]);
        
        $participationId = $this->db->lastInsertId();
        return $this->getParticipationById($participationId, $data['passenger_id']);
    }
    
    public function updateParticipationStatus(int $participationId, string $status, int $userId): array {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid status');
        }
        
        // Get participation details
        $stmt = $this->db->prepare('
            SELECT p.*, t.driver_id 
            FROM participations p
            JOIN trips t ON p.trip_id = t.id
            WHERE p.id = ?
        ');
        $stmt->execute([$participationId]);
        $participation = $stmt->fetch();
        
        if (!$participation) {
            throw new \Exception('Participation not found');
        }
        
        // Check permissions - only driver can confirm/cancel, passenger can cancel their own
        if ($status === 'confirmed' && $participation['driver_id'] != $userId) {
            throw new \Exception('Only the driver can confirm participations');
        }
        
        if ($status === 'cancelled' && $participation['driver_id'] != $userId && $participation['passenger_id'] != $userId) {
            throw new \Exception('Access denied');
        }
        
        $stmt = $this->db->prepare('UPDATE participations SET status = ? WHERE id = ?');
        $stmt->execute([$status, $participationId]);
        
        // Award eco credits when trip is completed
        if ($status === 'completed') {
            $this->awardEcoCredits($participation);
        }
        
        return $this->getParticipationById($participationId, $userId);
    }
    
    public function getParticipationById(int $participationId, int $userId): ?array {
        $stmt = $this->db->prepare('
            SELECT p.*, t.departure_city, t.arrival_city, t.departure_time, t.eco_points,
                   u_driver.name as driver_name, u_passenger.name as passenger_name
            FROM participations p
            JOIN trips t ON p.trip_id = t.id
            JOIN users u_driver ON t.driver_id = u_driver.id
            JOIN users u_passenger ON p.passenger_id = u_passenger.id
            WHERE p.id = ? AND (t.driver_id = ? OR p.passenger_id = ?)
        ');
        
        $stmt->execute([$participationId, $userId, $userId]);
        return $stmt->fetch() ?: null;
    }
    
    private function awardEcoCredits(array $participation): void {
        // Award credits to passenger based on trip eco points
        $stmt = $this->db->prepare('SELECT eco_points FROM trips WHERE id = ?');
        $stmt->execute([$participation['trip_id']]);
        $ecoPoints = $stmt->fetchColumn();
        
        if ($ecoPoints > 0) {
            $userService = new UserService();
            $userService->addEcoCredits(
                $participation['passenger_id'],
                $ecoPoints,
                'earned',
                'Eco credits earned from completed trip',
                $participation['trip_id']
            );
        }
    }
}