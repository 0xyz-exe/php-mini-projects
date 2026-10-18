<?php
declare(strict_types=1);

namespace RestApi;

use PDO;

class TaskApi
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function create(array $data): array
    {
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? null;
        $status = $data['status'] ?? 'pending';

        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Title is required');
        }

        if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $stmt = $this->pdo->prepare("INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $status]);
        $id = (int) $this->pdo->lastInsertId();

        return $this->getById($id);
    }

    public function getAll(int $page = 1, int $limit = 20, ?string $status = null): array
    {
        $offset = ($page - 1) * $limit;
        $params = [];
        $sql = "SELECT * FROM tasks WHERE 1=1";

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \RuntimeException('Task not found', 404);
        }

        return $result;
    }

    public function update(int $id, array $data): array
    {
        $updates = [];
        $params = [];

        if (isset($data['title'])) {
            if (empty(trim($data['title']))) {
                throw new \InvalidArgumentException('Title cannot be empty');
            }
            $updates[] = "title = ?";
            $params[] = $data['title'];
        }

        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], ['pending', 'in_progress', 'completed'])) {
                throw new \InvalidArgumentException('Invalid status');
            }
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }

        if (empty($updates)) {
            return $this->getById($id);
        }

        $params[] = $id;
        $sql = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Task not found', 404);
        }

        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Task not found', 404);
        }

        return true;
    }

    public function getTotalCount(?string $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM tasks WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
