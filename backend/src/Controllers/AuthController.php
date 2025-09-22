<?php

namespace EcoRide\Controllers;

use EcoRide\Services\AuthService;
use EcoRide\Services\EmailService;

class AuthController {
    private $authService;
    private $emailService;
    
    public function __construct() {
        $this->authService = new AuthService();
        $this->emailService = new EmailService();
    }
    
    public function register(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
            http_response_code(400);
            return ['error' => 'Email, password and name are required'];
        }
        
        try {
            $user = $this->authService->register($data);
            
            // Send verification email
            $this->emailService->sendVerificationEmail($user['email'], $user['verification_token']);
            
            return [
                'message' => 'Registration successful. Please check your email to verify your account.',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function login(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            return ['error' => 'Email and password are required'];
        }
        
        try {
            $result = $this->authService->login($data['email'], $data['password']);
            return $result;
        } catch (\Exception $e) {
            http_response_code(401);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function verifyEmail(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['token'])) {
            http_response_code(400);
            return ['error' => 'Verification token is required'];
        }
        
        try {
            $this->authService->verifyEmail($data['token']);
            return ['message' => 'Email verified successfully'];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function forgotPassword(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email'])) {
            http_response_code(400);
            return ['error' => 'Email is required'];
        }
        
        try {
            $token = $this->authService->generatePasswordResetToken($data['email']);
            $this->emailService->sendPasswordResetEmail($data['email'], $token);
            
            return ['message' => 'Password reset email sent'];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    public function resetPassword(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['token']) || !isset($data['password'])) {
            http_response_code(400);
            return ['error' => 'Token and new password are required'];
        }
        
        try {
            $this->authService->resetPassword($data['token'], $data['password']);
            return ['message' => 'Password reset successfully'];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
}