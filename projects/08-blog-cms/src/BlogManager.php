<?php
declare(strict_types=1);

namespace Blog;

use PDO;

class BlogManager
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function createPost(string $title, string $content, string $status = 'draft', ?string $category = null, array $tags = []): int
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        if (!in_array($status, ['draft', 'published'])) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $slug = $this->generateSlug($title);
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        $tagsJson = json_encode($tags);

        $stmt = $this->pdo->prepare(
            "INSERT INTO posts (title, slug, content, status, category, tags, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$title, $slug, $content, $status, $category, $tagsJson, $publishedAt]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getPosts(?string $status = null, ?string $category = null, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $sql = "SELECT * FROM posts WHERE 1=1";
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE slug = ?");
        $stmt->execute([$slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $updates = [];
        $params = [];
        
        foreach (['title', 'content', 'excerpt', 'status', 'category'] as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['tags'])) {
            $updates[] = "tags = ?";
            $params[] = json_encode($data['tags']);
        }
        
        if (empty($updates)) {
            return false;
        }
        
        if (isset($data['status']) && $data['status'] === 'published') {
            $updates[] = "published_at = ?";
            $params[] = date('Y-m-d H:i:s');
        }
        
        $params[] = $id;
        $sql = "UPDATE posts SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    public function search(string $query, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%$query%";
        
        $stmt = $this->pdo->prepare(
            "SELECT * FROM posts WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$searchTerm, $searchTerm, $perPage, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
