<?php
declare(strict_types=1);

namespace Auth;

use PDO;

class Auth
{
    private PDO $pdo;
    private int $maxAttempts = 5;
    private int $lockoutMinutes = 15;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function register(string $email, string $password): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
            $stmt->execute([$email, $hash]);
            return true;
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint')) {
                throw new \RuntimeException('Email already registered');
            }
            throw $e;
        }
    }

    public function login(string $email, string $password, string $ipAddress = '127.0.0.1'): ?int
    {
        if ($this->isRateLimited($email, $ipAddress)) {
            throw new \RuntimeException('Too many login attempts. Try again later.');
        }

        $stmt = $this->pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->recordAttempt($email, $ipAddress);
            return null;
        }

        $this->clearAttempts($email, $ipAddress);
        return (int) $user['id'];
    }

    private function isRateLimited(string $email, string $ipAddress): bool
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$this->lockoutMinutes} minutes"));
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM login_attempts 
             WHERE (email = ? OR ip_address = ?) AND attempted_at > ?"
        );
        $stmt->execute([$email, $ipAddress, $cutoff]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] >= $this->maxAttempts;
    }

    private function recordAttempt(string $email, string $ipAddress): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
        $stmt->execute([$email, $ipAddress]);
    }

    private function clearAttempts(string $email, string $ipAddress): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE email = ? OR ip_address = ?");
        $stmt->execute([$email, $ipAddress]);
    }

    public function startSession(int $userId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict',
                'use_strict_mode' => true
            ]);
        }
        
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
    }

    public function isLoggedIn(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']);
    }

    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
