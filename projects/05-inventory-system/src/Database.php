<?php
declare(strict_types=1);

namespace Inventory;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct(string $dbPath = 'inventory.db')
    {
        $this->pdo = new PDO("sqlite:$dbPath");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->init();
    }

    private function init(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            unit_price REAL NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            low_stock_threshold INTEGER DEFAULT 10,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");
        
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            notes TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )");
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
