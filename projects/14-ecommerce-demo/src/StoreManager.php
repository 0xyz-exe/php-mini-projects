<?php
declare(strict_types=1);

namespace Ecommerce;

use PDO;

class StoreManager
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function getProducts(?string $category = null, ?string $search = null): array
    {
        $sql = "SELECT * FROM products WHERE stock > 0";
        $params = [];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        if ($search) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createOrder(string $customerName, string $customerEmail, array $items): int
    {
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }
        if (empty($items)) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        $this->pdo->beginTransaction();
        try {
            $totalAmount = 0;
            
            // Validate stock and calculate total
            foreach ($items as $item) {
                $stmt = $this->pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    throw new \RuntimeException('Product not found');
                }
                if ($product['stock'] < $item['quantity']) {
                    throw new \RuntimeException('Insufficient stock');
                }
                
                $totalAmount += $product['price'] * $item['quantity'];
            }
            
            // Create order
            $stmt = $this->pdo->prepare(
                "INSERT INTO orders (customer_name, customer_email, total_amount) VALUES (?, ?, ?)"
            );
            $stmt->execute([$customerName, $customerEmail, $totalAmount]);
            $orderId = (int) $this->pdo->lastInsertId();
            
            // Add order items and update stock
            foreach ($items as $item) {
                $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $price = $stmt->fetchColumn();
                
                $stmt = $this->pdo->prepare(
                    "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $price]);
                
                $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            $this->pdo->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getOrder(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }
        
        $stmt = $this->pdo->prepare(
            "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?"
        );
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $order;
    }

    public function addProduct(string $name, float $price, int $stock, ?string $description = null, ?string $category = null): int
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Name required');
        }
        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $price, $stock, $category]);
        return (int) $this->pdo->lastInsertId();
    }
}
