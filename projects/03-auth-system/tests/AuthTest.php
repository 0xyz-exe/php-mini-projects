<?php
declare(strict_types=1);

namespace Auth\Tests;

use PHPUnit\Framework\TestCase;
use Auth\Database;
use Auth\Auth;

class AuthTest extends TestCase
{
    private Auth $auth;
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/test_auth_' . uniqid() . '.db';
        $db = new Database($this->dbPath);
        $this->auth = new Auth($db);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    public function testRegisterSuccess(): void
    {
        $result = $this->auth->register('test@example.com', 'password123');
        $this->assertTrue($result);
    }

    public function testRegisterInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->auth->register('invalid-email', 'password123');
    }

    public function testRegisterShortPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->auth->register('test@example.com', 'short');
    }

    public function testRegisterDuplicate(): void
    {
        $this->auth->register('test@example.com', 'password123');
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Email already registered');
        $this->auth->register('test@example.com', 'password456');
    }

    public function testLoginSuccess(): void
    {
        $this->auth->register('test@example.com', 'password123');
        $userId = $this->auth->login('test@example.com', 'password123');
        
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
    }

    public function testLoginWrongPassword(): void
    {
        $this->auth->register('test@example.com', 'password123');
        $userId = $this->auth->login('test@example.com', 'wrongpassword');
        
        $this->assertNull($userId);
    }

    public function testLoginNonexistentUser(): void
    {
        $userId = $this->auth->login('nonexistent@example.com', 'password123');
        $this->assertNull($userId);
    }

    public function testRateLimiting(): void
    {
        $this->auth->register('test@example.com', 'password123');
        
        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->auth->login('test@example.com', 'wrongpassword', '192.168.1.1');
        }
        
        // 6th attempt should be rate limited
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Too many login attempts');
        $this->auth->login('test@example.com', 'password123', '192.168.1.1');
    }

    public function testCsrfTokenGeneration(): void
    {
        $token1 = $this->auth->generateCsrfToken();
        $token2 = $this->auth->generateCsrfToken();
        
        $this->assertNotEmpty($token1);
        $this->assertEquals($token1, $token2); // Should be same in session
    }

    public function testCsrfTokenValidation(): void
    {
        $token = $this->auth->generateCsrfToken();
        $this->assertTrue($this->auth->validateCsrfToken($token));
        $this->assertFalse($this->auth->validateCsrfToken('invalid-token'));
    }
}
