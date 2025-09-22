<?php

namespace EcoRide\Tests\Unit;

use PHPUnit\Framework\TestCase;
use EcoRide\Services\AuthService;

class AuthServiceTest extends TestCase {
    private $authService;
    
    protected function setUp(): void {
        $this->authService = new AuthService();
    }
    
    public function testValidateEmailFormat(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid email format');
        
        $this->authService->register([
            'email' => 'invalid-email',
            'password' => 'password123',
            'name' => 'Test User'
        ]);
    }
    
    public function testPasswordLength(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must be at least 6 characters long');
        
        $this->authService->register([
            'email' => 'test@example.com',
            'password' => '123',
            'name' => 'Test User'
        ]);
    }
}