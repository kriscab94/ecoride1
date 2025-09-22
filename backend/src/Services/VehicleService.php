<?php

namespace EcoRide\Services;

use EcoRide\Core\Database;

class VehicleService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUserVehicles(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT * FROM vehicles 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ');
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function createVehicle(array $data): array {
        $validFuelTypes = ['gasoline', 'diesel', 'hybrid', 'electric'];
        if (!in_array($data['fuel_type'], $validFuelTypes)) {
            throw new \Exception('Invalid fuel type');
        }
        
        if ($data['year'] < 1990 || $data['year'] > date('Y') + 1) {
            throw new \Exception('Invalid year');
        }
        
        if ($data['seats'] < 2 || $data['seats'] > 9) {
            throw new \Exception('Seats must be between 2 and 9');
        }
        
        $stmt = $this->db->prepare('
            INSERT INTO vehicles (user_id, make, model, year, color, license_plate, seats, fuel_type, consumption_per_100km)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['user_id'],
            $data['make'],
            $data['model'],
            $data['year'],
            $data['color'] ?? null,
            $data['license_plate'] ?? null,
            $data['seats'],
            $data['fuel_type'],
            $data['consumption_per_100km'] ?? null
        ]);
        
        $vehicleId = $this->db->lastInsertId();
        return $this->getVehicleById($vehicleId);
    }
    
    public function updateVehicle(int $vehicleId, array $data, int $userId): array {
        // Check ownership
        $stmt = $this->db->prepare('SELECT id FROM vehicles WHERE id = ? AND user_id = ?');
        $stmt->execute([$vehicleId, $userId]);
        if (!$stmt->fetch()) {
            throw new \Exception('Vehicle not found or access denied');
        }
        
        $allowedFields = ['make', 'model', 'year', 'color', 'license_plate', 'seats', 'fuel_type', 'consumption_per_100km'];
        $updateFields = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'fuel_type') {
                    $validFuelTypes = ['gasoline', 'diesel', 'hybrid', 'electric'];
                    if (!in_array($data[$field], $validFuelTypes)) {
                        throw new \Exception('Invalid fuel type');
                    }
                }
                
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updateFields)) {
            $params[] = $vehicleId;
            $sql = "UPDATE vehicles SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        
        return $this->getVehicleById($vehicleId);
    }
    
    public function deleteVehicle(int $vehicleId, int $userId): void {
        // Check ownership
        $stmt = $this->db->prepare('SELECT id FROM vehicles WHERE id = ? AND user_id = ?');
        $stmt->execute([$vehicleId, $userId]);
        if (!$stmt->fetch()) {
            throw new \Exception('Vehicle not found or access denied');
        }
        
        // Check if vehicle is used in any active trips
        $stmt = $this->db->prepare('SELECT id FROM trips WHERE vehicle_id = ? AND status = "active"');
        $stmt->execute([$vehicleId]);
        if ($stmt->fetch()) {
            throw new \Exception('Cannot delete vehicle with active trips');
        }
        
        $stmt = $this->db->prepare('DELETE FROM vehicles WHERE id = ?');
        $stmt->execute([$vehicleId]);
    }
    
    private function getVehicleById(int $vehicleId): array {
        $stmt = $this->db->prepare('SELECT * FROM vehicles WHERE id = ?');
        $stmt->execute([$vehicleId]);
        $vehicle = $stmt->fetch();
        
        if (!$vehicle) {
            throw new \Exception('Vehicle not found');
        }
        
        return $vehicle;
    }
}