<?php
declare(strict_types=1);

namespace JobTracker;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct(string $dbPath = 'jobs.db')
    {
        $this->pdo = new PDO("sqlite:$dbPath");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->init();
    }

    private function init(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS applications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company TEXT NOT NULL,
            position TEXT NOT NULL,
            location TEXT,
            application_date TEXT NOT NULL,
            status TEXT DEFAULT 'applied',
            salary_range TEXT,
            job_url TEXT,
            contact_name TEXT,
            contact_email TEXT,
            notes TEXT,
            follow_up_date TEXT,
            interview_date TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
