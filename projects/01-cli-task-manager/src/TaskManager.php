<?php
declare(strict_types=1);

namespace TaskManager;

use PDO;

class TaskManager
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function create(string $title, ?string $description = null, string $priority = 'medium', ?string $dueDate = null): int
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            throw new \InvalidArgumentException('Priority must be low, medium, or high');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tasks (title, description, priority, due_date) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$title, $description, $priority, $dueDate]);
        return (int) $this->pdo->lastInsertId();
    }

    public function list(?string $status = null, ?string $priority = null): array
    {
        $sql = "SELECT * FROM tasks WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        if ($priority) {
            $sql .= " AND priority = ?";
            $params[] = $priority;
        }
        
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function complete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, ?string $title = null, ?string $description = null, ?string $priority = null, ?string $dueDate = null): bool
    {
        $updates = [];
        $params = [];
        
        if ($title !== null) {
            $updates[] = "title = ?";
            $params[] = $title;
        }
        if ($description !== null) {
            $updates[] = "description = ?";
            $params[] = $description;
        }
        if ($priority !== null) {
            if (!in_array($priority, ['low', 'medium', 'high'])) {
                throw new \InvalidArgumentException('Priority must be low, medium, or high');
            }
            $updates[] = "priority = ?";
            $params[] = $priority;
        }
        if ($dueDate !== null) {
            $updates[] = "due_date = ?";
            $params[] = $dueDate;
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }
}
