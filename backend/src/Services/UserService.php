<?php

namespace EcoRide\Services;

use EcoRide\Core\Database;

class UserService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUserProfile(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT id, email, name, phone, bio, profile_image, eco_credits, 
                   email_verified, created_at,
                   (SELECT AVG(rating) FROM reviews WHERE reviewed_id = ?) as average_rating,
                   (SELECT COUNT(*) FROM reviews WHERE reviewed_id = ?) as review_count
            FROM users WHERE id = ?
        ');
        
        $stmt->execute([$userId, $userId, $userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Get vehicles count
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM vehicles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $user['vehicles_count'] = $stmt->fetchColumn();
        
        // Get trips count as driver
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM trips WHERE driver_id = ?');
        $stmt->execute([$userId]);
        $user['trips_as_driver'] = $stmt->fetchColumn();
        
        // Get trips count as passenger
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM participations WHERE passenger_id = ? AND status = "completed"');
        $stmt->execute([$userId]);
        $user['trips_as_passenger'] = $stmt->fetchColumn();
        
        return $user;
    }
    
    public function updateUserProfile(int $userId, array $data): array {
        $allowedFields = ['name', 'phone', 'bio', 'profile_image'];
        $updateFields = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            throw new \Exception('No valid fields to update');
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $this->getUserProfile($userId);
    }
    
    public function getUserCredits(int $userId): array {
        $stmt = $this->db->prepare('SELECT eco_credits FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $credits = $stmt->fetchColumn();
        
        // Get recent transactions
        $stmt = $this->db->prepare('
            SELECT et.*, t.departure_city, t.arrival_city 
            FROM eco_transactions et
            LEFT JOIN trips t ON et.trip_id = t.id
            WHERE et.user_id = ?
            ORDER BY et.created_at DESC
            LIMIT 10
        ');
        $stmt->execute([$userId]);
        $transactions = $stmt->fetchAll();
        
        return [
            'current_credits' => $credits,
            'recent_transactions' => $transactions
        ];
    }
    
    public function addEcoCredits(int $userId, int $amount, string $type, string $description, ?int $tripId = null): void {
        $this->db->beginTransaction();
        try {
            // Add transaction record
            $stmt = $this->db->prepare('
                INSERT INTO eco_transactions (user_id, trip_id, type, amount, description)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$userId, $tripId, $type, $amount, $description]);
            
            // Update user credits
            $stmt = $this->db->prepare('UPDATE users SET eco_credits = eco_credits + ? WHERE id = ?');
            $stmt->execute([$amount, $userId]);
            
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}