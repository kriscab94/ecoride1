<?php

namespace EcoRide\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use EcoRide\Core\Database;

class AuthMiddleware {
    public static function authenticate(): array {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization token required']);
            exit;
        }
        
        $token = $matches[1];
        
        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-in-production';
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            
            // Get user from database
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id, email, name, eco_credits FROM users WHERE id = ? AND email_verified = TRUE');
            $stmt->execute([$decoded->user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            return $user;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }
    }
}