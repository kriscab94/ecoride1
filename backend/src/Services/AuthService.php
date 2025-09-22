<?php

namespace EcoRide\Services;

use Firebase\JWT\JWT;
use EcoRide\Core\Database;

class AuthService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register(array $data): array {
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email format');
        }
        
        // Check if email already exists
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            throw new \Exception('Email already registered');
        }
        
        // Validate password strength
        if (strlen($data['password']) < 6) {
            throw new \Exception('Password must be at least 6 characters long');
        }
        
        // Hash password and generate verification token
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));
        
        // Insert user
        $stmt = $this->db->prepare('
            INSERT INTO users (email, password, name, phone, bio, verification_token) 
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['email'],
            $hashedPassword,
            $data['name'],
            $data['phone'] ?? null,
            $data['bio'] ?? null,
            $verificationToken
        ]);
        
        $userId = $this->db->lastInsertId();
        
        return [
            'id' => $userId,
            'email' => $data['email'],
            'name' => $data['name'],
            'verification_token' => $verificationToken
        ];
    }
    
    public function login(string $email, string $password): array {
        $stmt = $this->db->prepare('SELECT id, email, password, name, email_verified FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            throw new \Exception('Invalid email or password');
        }
        
        if (!$user['email_verified']) {
            throw new \Exception('Please verify your email before logging in');
        }
        
        // Generate JWT token
        $secret = $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-in-production';
        $expire = time() + ($_ENV['JWT_EXPIRE'] ?? 3600);
        
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'exp' => $expire
        ];
        
        $token = JWT::encode($payload, $secret, 'HS256');
        
        return [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ]
        ];
    }
    
    public function verifyEmail(string $token): void {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE verification_token = ? AND email_verified = FALSE');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new \Exception('Invalid or expired verification token');
        }
        
        $stmt = $this->db->prepare('UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE id = ?');
        $stmt->execute([$user['id']]);
    }
    
    public function generatePasswordResetToken(string $email): string {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new \Exception('Email not found');
        }
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        
        $stmt = $this->db->prepare('UPDATE users SET verification_token = ? WHERE id = ?');
        $stmt->execute([$token, $user['id']]);
        
        return $token;
    }
    
    public function resetPassword(string $token, string $newPassword): void {
        if (strlen($newPassword) < 6) {
            throw new \Exception('Password must be at least 6 characters long');
        }
        
        $stmt = $this->db->prepare('SELECT id FROM users WHERE verification_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new \Exception('Invalid or expired reset token');
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare('UPDATE users SET password = ?, verification_token = NULL WHERE id = ?');
        $stmt->execute([$hashedPassword, $user['id']]);
    }
}