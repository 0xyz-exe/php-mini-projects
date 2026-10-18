<?php
declare(strict_types=1);

namespace ExpenseTracker;

use PDO;

class ExpenseTracker
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function addTransaction(string $type, float $amount, string $category, string $date, ?string $description = null): int
    {
        if (!in_array($type, ['income', 'expense'])) {
            throw new \InvalidArgumentException('Type must be income or expense');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
        if (empty(trim($category))) {
            throw new \InvalidArgumentException('Category cannot be empty');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO transactions (type, amount, category, date, description) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$type, $amount, $category, $date, $description]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getTransactions(?string $type = null, ?string $category = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT * FROM transactions WHERE 1=1";
        $params = [];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        if ($startDate) {
            $sql .= " AND date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlySummary(string $month): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT type, SUM(amount) as total FROM transactions 
             WHERE strftime('%Y-%m', date) = ? 
             GROUP BY type"
        );
        $stmt->execute([$month]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $summary = ['income' => 0.0, 'expense' => 0.0, 'balance' => 0.0];
        foreach ($results as $row) {
            $summary[$row['type']] = (float) $row['total'];
        }
        $summary['balance'] = $summary['income'] - $summary['expense'];
        
        return $summary;
    }

    public function getCategoryBreakdown(?string $month = null): array
    {
        $sql = "SELECT category, type, SUM(amount) as total FROM transactions";
        $params = [];
        
        if ($month) {
            $sql .= " WHERE strftime('%Y-%m', date) = ?";
            $params[] = $month;
        }
        
        $sql .= " GROUP BY category, type ORDER BY total DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exportToCSV(string $filename): void
    {
        $transactions = $this->getTransactions();
        $fp = fopen($filename, 'w');
        
        if ($fp === false) {
            throw new \RuntimeException("Cannot open file: $filename");
        }
        
        fputcsv($fp, ['ID', 'Type', 'Amount', 'Category', 'Date', 'Description']);
        
        foreach ($transactions as $t) {
            fputcsv($fp, [$t['id'], $t['type'], $t['amount'], $t['category'], $t['date'], $t['description'] ?? '']);
        }
        
        fclose($fp);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
