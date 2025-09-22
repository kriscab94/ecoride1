<?php

namespace EcoRide\Services;

use EcoRide\Core\Database;

class TripService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function searchTrips(string $from, string $to, string $date, int $seats, int $page, int $limit): array {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT t.*, u.name as driver_name, u.email as driver_email,
                   v.make, v.model, v.year, v.fuel_type,
                   (SELECT AVG(rating) FROM reviews WHERE reviewed_id = t.driver_id) as driver_rating
            FROM trips t
            JOIN users u ON t.driver_id = u.id
            JOIN vehicles v ON t.vehicle_id = v.id
            WHERE t.status = 'active' AND t.available_seats >= ?
        ";
        
        $params = [$seats];
        
        if ($from) {
            $sql .= " AND (t.departure_city LIKE ? OR t.departure_address LIKE ?)";
            $params[] = "%$from%";
            $params[] = "%$from%";
        }
        
        if ($to) {
            $sql .= " AND (t.arrival_city LIKE ? OR t.arrival_address LIKE ?)";
            $params[] = "%$to%";
            $params[] = "%$to%";
        }
        
        if ($date) {
            $sql .= " AND DATE(t.departure_time) = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY t.departure_time ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $trips = $stmt->fetchAll();
        
        // Count total results
        $countSql = str_replace("SELECT t.*, u.name as driver_name, u.email as driver_email, v.make, v.model, v.year, v.fuel_type, (SELECT AVG(rating) FROM reviews WHERE reviewed_id = t.driver_id) as driver_rating", "SELECT COUNT(*)", $sql);
        $countSql = str_replace(" ORDER BY t.departure_time ASC LIMIT ? OFFSET ?", "", $countSql);
        array_pop($params); // Remove offset
        array_pop($params); // Remove limit
        
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        return [
            'trips' => $trips,
            'pagination' => [
                'current_page' => $page,
                'total' => $total,
                'per_page' => $limit,
                'last_page' => ceil($total / $limit)
            ]
        ];
    }
    
    public function createTrip(array $data): array {
        // Validate vehicle belongs to user
        $stmt = $this->db->prepare('SELECT id FROM vehicles WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['vehicle_id'], $data['driver_id']]);
        if (!$stmt->fetch()) {
            throw new \Exception('Vehicle not found or does not belong to user');
        }
        
        // Calculate eco points based on vehicle type and distance
        $ecoPoints = $this->calculateEcoPoints($data['vehicle_id'], $data['distance_km'] ?? 0);
        
        $stmt = $this->db->prepare('
            INSERT INTO trips (
                driver_id, vehicle_id, departure_city, departure_address, departure_lat, departure_lng,
                arrival_city, arrival_address, arrival_lat, arrival_lng, departure_time, arrival_time,
                available_seats, price_per_seat, description, distance_km, eco_points
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['driver_id'],
            $data['vehicle_id'],
            $data['departure_city'],
            $data['departure_address'],
            $data['departure_lat'] ?? null,
            $data['departure_lng'] ?? null,
            $data['arrival_city'],
            $data['arrival_address'],
            $data['arrival_lat'] ?? null,
            $data['arrival_lng'] ?? null,
            $data['departure_time'],
            $data['arrival_time'] ?? null,
            $data['available_seats'],
            $data['price_per_seat'],
            $data['description'] ?? null,
            $data['distance_km'] ?? null,
            $ecoPoints
        ]);
        
        $tripId = $this->db->lastInsertId();
        return $this->getTripById($tripId);
    }
    
    public function getTripById(int $tripId): ?array {
        $stmt = $this->db->prepare('
            SELECT t.*, u.name as driver_name, u.email as driver_email, u.phone as driver_phone,
                   v.make, v.model, v.year, v.color, v.fuel_type, v.seats as vehicle_seats,
                   (SELECT AVG(rating) FROM reviews WHERE reviewed_id = t.driver_id) as driver_rating,
                   (SELECT COUNT(*) FROM reviews WHERE reviewed_id = t.driver_id) as driver_review_count
            FROM trips t
            JOIN users u ON t.driver_id = u.id
            JOIN vehicles v ON t.vehicle_id = v.id
            WHERE t.id = ?
        ');
        
        $stmt->execute([$tripId]);
        $trip = $stmt->fetch();
        
        if ($trip) {
            // Get participations
            $stmt = $this->db->prepare('
                SELECT p.*, u.name as passenger_name, u.email as passenger_email
                FROM participations p
                JOIN users u ON p.passenger_id = u.id
                WHERE p.trip_id = ? AND p.status != "cancelled"
            ');
            $stmt->execute([$tripId]);
            $trip['participations'] = $stmt->fetchAll();
        }
        
        return $trip ?: null;
    }
    
    public function updateTrip(int $tripId, array $data, int $userId): array {
        // Check if user owns the trip
        $stmt = $this->db->prepare('SELECT id FROM trips WHERE id = ? AND driver_id = ?');
        $stmt->execute([$tripId, $userId]);
        if (!$stmt->fetch()) {
            throw new \Exception('Trip not found or access denied');
        }
        
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['departure_time', 'arrival_time', 'available_seats', 'price_per_seat', 'description'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updateFields)) {
            $params[] = $tripId;
            $sql = "UPDATE trips SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        
        return $this->getTripById($tripId);
    }
    
    public function cancelTrip(int $tripId, int $userId): void {
        $stmt = $this->db->prepare('SELECT id FROM trips WHERE id = ? AND driver_id = ?');
        $stmt->execute([$tripId, $userId]);
        if (!$stmt->fetch()) {
            throw new \Exception('Trip not found or access denied');
        }
        
        $this->db->beginTransaction();
        try {
            // Update trip status
            $stmt = $this->db->prepare('UPDATE trips SET status = "cancelled" WHERE id = ?');
            $stmt->execute([$tripId]);
            
            // Cancel all participations
            $stmt = $this->db->prepare('UPDATE participations SET status = "cancelled" WHERE trip_id = ?');
            $stmt->execute([$tripId]);
            
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getUserTripsAsDriver(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT t.*, v.make, v.model,
                   (SELECT COUNT(*) FROM participations WHERE trip_id = t.id AND status = "confirmed") as confirmed_passengers
            FROM trips t
            JOIN vehicles v ON t.vehicle_id = v.id
            WHERE t.driver_id = ?
            ORDER BY t.departure_time DESC
        ');
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getUserTripsAsPassenger(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT t.*, p.status as participation_status, p.seats_reserved, p.total_price,
                   u.name as driver_name, v.make, v.model
            FROM participations p
            JOIN trips t ON p.trip_id = t.id
            JOIN users u ON t.driver_id = u.id
            JOIN vehicles v ON t.vehicle_id = v.id
            WHERE p.passenger_id = ?
            ORDER BY t.departure_time DESC
        ');
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    private function calculateEcoPoints(int $vehicleId, float $distance): int {
        $stmt = $this->db->prepare('SELECT fuel_type FROM vehicles WHERE id = ?');
        $stmt->execute([$vehicleId]);
        $vehicle = $stmt->fetch();
        
        if (!$vehicle) return 0;
        
        $multiplier = match($vehicle['fuel_type']) {
            'electric' => 3.0,
            'hybrid' => 2.0,
            'diesel' => 1.2,
            'gasoline' => 1.0,
            default => 1.0
        };
        
        return max(1, intval($distance * $multiplier * 0.1));
    }
}