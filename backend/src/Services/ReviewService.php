<?php

namespace EcoRide\Services;

use EcoRide\Core\Database;

class ReviewService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createReview(array $data): array {
        // Validate rating
        if ($data['rating'] < 1 || $data['rating'] > 5) {
            throw new \Exception('Rating must be between 1 and 5');
        }
        
        // Validate review type
        $validTypes = ['driver_to_passenger', 'passenger_to_driver'];
        if (!in_array($data['type'], $validTypes)) {
            throw new \Exception('Invalid review type');
        }
        
        // Check if trip exists and is completed
        $stmt = $this->db->prepare('
            SELECT driver_id FROM trips 
            WHERE id = ? AND status = "completed"
        ');
        $stmt->execute([$data['trip_id']]);
        $trip = $stmt->fetch();
        
        if (!$trip) {
            throw new \Exception('Trip not found or not completed');
        }
        
        // Check if participation exists for passenger reviews
        if ($data['type'] === 'passenger_to_driver') {
            $stmt = $this->db->prepare('
                SELECT id FROM participations 
                WHERE trip_id = ? AND passenger_id = ? AND status = "completed"
            ');
            $stmt->execute([$data['trip_id'], $data['reviewer_id']]);
            if (!$stmt->fetch()) {
                throw new \Exception('You must have completed this trip to leave a review');
            }
            
            // Reviewed user should be the driver
            if ($data['reviewed_id'] != $trip['driver_id']) {
                throw new \Exception('Invalid reviewed user');
            }
        } else {
            // Driver reviewing passenger
            if ($data['reviewer_id'] != $trip['driver_id']) {
                throw new \Exception('Only the driver can review passengers');
            }
            
            // Check if reviewed user was a passenger
            $stmt = $this->db->prepare('
                SELECT id FROM participations 
                WHERE trip_id = ? AND passenger_id = ? AND status = "completed"
            ');
            $stmt->execute([$data['trip_id'], $data['reviewed_id']]);
            if (!$stmt->fetch()) {
                throw new \Exception('Reviewed user was not a passenger on this trip');
            }
        }
        
        // Check if review already exists
        $stmt = $this->db->prepare('
            SELECT id FROM reviews 
            WHERE trip_id = ? AND reviewer_id = ? AND reviewed_id = ? AND type = ?
        ');
        $stmt->execute([$data['trip_id'], $data['reviewer_id'], $data['reviewed_id'], $data['type']]);
        if ($stmt->fetch()) {
            throw new \Exception('Review already exists');
        }
        
        // Create review
        $stmt = $this->db->prepare('
            INSERT INTO reviews (trip_id, reviewer_id, reviewed_id, rating, comment, type)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['trip_id'],
            $data['reviewer_id'],
            $data['reviewed_id'],
            $data['rating'],
            $data['comment'] ?? null,
            $data['type']
        ]);
        
        $reviewId = $this->db->lastInsertId();
        return $this->getReviewById($reviewId);
    }
    
    public function getUserReviews(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT r.*, u_reviewer.name as reviewer_name, u_reviewed.name as reviewed_name,
                   t.departure_city, t.arrival_city, t.departure_time
            FROM reviews r
            JOIN users u_reviewer ON r.reviewer_id = u_reviewer.id
            JOIN users u_reviewed ON r.reviewed_id = u_reviewed.id
            JOIN trips t ON r.trip_id = t.id
            WHERE r.reviewed_id = ?
            ORDER BY r.created_at DESC
        ');
        
        $stmt->execute([$userId]);
        $reviews = $stmt->fetchAll();
        
        // Calculate average rating
        $stmt = $this->db->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE reviewed_id = ?');
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();
        
        return [
            'reviews' => $reviews,
            'average_rating' => round($stats['avg_rating'], 1),
            'total_reviews' => $stats['total_reviews']
        ];
    }
    
    private function getReviewById(int $reviewId): array {
        $stmt = $this->db->prepare('
            SELECT r.*, u_reviewer.name as reviewer_name, u_reviewed.name as reviewed_name
            FROM reviews r
            JOIN users u_reviewer ON r.reviewer_id = u_reviewer.id
            JOIN users u_reviewed ON r.reviewed_id = u_reviewed.id
            WHERE r.id = ?
        ');
        
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();
        
        if (!$review) {
            throw new \Exception('Review not found');
        }
        
        return $review;
    }
}