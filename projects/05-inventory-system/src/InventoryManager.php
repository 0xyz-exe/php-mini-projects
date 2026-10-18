<?php
declare(strict_types=1);

namespace Inventory;

use PDO;

class InventoryManager
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function addProduct(string $name, string $category, float $unitPrice, int $quantity, int $lowStockThreshold = 10): int
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        if ($unitPrice < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, category, unit_price, quantity, low_stock_threshold) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $category, $unitPrice, $quantity, $lowStockThreshold]);
        return (int) $this->pdo->lastInsertId();
    }

    public function stockIn(int $productId, int $quantity, string $notes = ''): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $productId]);
            
            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('Product not found');
            }

            $stmt = $this->pdo->prepare("INSERT INTO movements (product_id, type, quantity, notes) VALUES (?, 'in', ?, ?)");
            $stmt->execute([$productId, $quantity, $notes]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function stockOut(int $productId, int $quantity, string $notes = ''): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT quantity FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $current = $stmt->fetchColumn();
            
            if ($current === false) {
                throw new \RuntimeException('Product not found');
            }
            
            if ($current < $quantity) {
                throw new \RuntimeException('Insufficient stock');
            }

            $stmt = $this->pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $productId]);

            $stmt = $this->pdo->prepare("INSERT INTO movements (product_id, type, quantity, notes) VALUES (?, 'out', ?, ?)");
            $stmt->execute([$productId, $quantity, $notes]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getLowStockProducts(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM products WHERE quantity <= low_stock_threshold ORDER BY quantity ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventoryValue(): float
    {
        $stmt = $this->pdo->query("SELECT SUM(quantity * unit_price) as total FROM products");
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public function getMovements(int $productId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM movements WHERE product_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$productId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exportCSV(string $filename): void
    {
        $stmt = $this->pdo->query("SELECT * FROM products ORDER BY name");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fp = fopen($filename, 'w');
        if ($fp === false) {
            throw new \RuntimeException("Cannot open file: $filename");
        }
        
        fputcsv($fp, ['ID', 'Name', 'Category', 'Unit Price', 'Quantity', 'Value', 'Low Stock Alert']);
        
        foreach ($products as $p) {
            $value = $p['quantity'] * $p['unit_price'];
            $alert = $p['quantity'] <= $p['low_stock_threshold'] ? 'YES' : 'NO';
            fputcsv($fp, [$p['id'], $p['name'], $p['category'], $p['unit_price'], $p['quantity'], $value, $alert]);
        }
        
        fclose($fp);
    }
}
