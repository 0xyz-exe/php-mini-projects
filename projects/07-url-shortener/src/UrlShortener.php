<?php
declare(strict_types=1);

namespace UrlShortener;

use PDO;

class UrlShortener
{
    private PDO $pdo;
    private const ALLOWED_SCHEMES = ['http', 'https'];

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function shorten(string $url, ?string $customCode = null): string
    {
        if (!$this->isValidUrl($url)) {
            throw new \InvalidArgumentException('Invalid or unsafe URL');
        }

        $shortCode = $customCode ?? $this->generateCode();
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO urls (short_code, original_url) VALUES (?, ?)");
            $stmt->execute([$shortCode, $url]);
            return $shortCode;
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint')) {
                if ($customCode) {
                    throw new \RuntimeException('Custom code already in use');
                }
                return $this->shorten($url); // Retry with new random code
            }
            throw $e;
        }
    }

    public function resolve(string $shortCode): ?string
    {
        $stmt = $this->pdo->prepare("SELECT original_url FROM urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->incrementClicks($shortCode);
            return $result['original_url'];
        }
        
        return null;
    }

    private function incrementClicks(string $shortCode): void
    {
        $stmt = $this->pdo->prepare("UPDATE urls SET clicks = clicks + 1 WHERE short_code = ?");
        $stmt->execute([$shortCode]);
    }

    public function getStats(string $shortCode): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function generateCode(int $length = 6): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    private function isValidUrl(string $url): bool
    {
        $parsed = parse_url($url);
        
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }
        
        if (!in_array(strtolower($parsed['scheme']), self::ALLOWED_SCHEMES)) {
            return false;
        }
        
        // Prevent javascript:, data:, file: schemes
        return true;
    }

    public function delete(string $shortCode): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        return $stmt->rowCount() > 0;
    }
}
