<?php
declare(strict_types=1);

namespace Library;

use PDO;

class LibraryManager
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function addBook(string $title, string $author, ?string $isbn = null, ?string $category = null): int
    {
        if (empty(trim($title)) || empty(trim($author))) {
            throw new \InvalidArgumentException('Title and author required');
        }

        $stmt = $this->pdo->prepare("INSERT INTO books (title, author, isbn, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $author, $isbn, $category]);
        return (int) $this->pdo->lastInsertId();
    }

    public function addMember(string $name, string $email): int
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }

        $stmt = $this->pdo->prepare("INSERT INTO members (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        return (int) $this->pdo->lastInsertId();
    }

    public function borrowBook(int $bookId, int $memberId, int $daysUntilDue = 14): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT status FROM books WHERE id = ?");
            $stmt->execute([$bookId]);
            $status = $stmt->fetchColumn();
            
            if ($status === false) {
                throw new \RuntimeException('Book not found');
            }
            if ($status !== 'available') {
                throw new \RuntimeException('Book not available');
            }

            $dueDate = date('Y-m-d', strtotime("+$daysUntilDue days"));
            
            $stmt = $this->pdo->prepare("INSERT INTO transactions (book_id, member_id, due_date) VALUES (?, ?, ?)");
            $stmt->execute([$bookId, $memberId, $dueDate]);
            $transactionId = (int) $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
            $stmt->execute([$bookId]);
            
            $this->pdo->commit();
            return $transactionId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function returnBook(int $transactionId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT book_id, returned_at FROM transactions WHERE id = ?");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                throw new \RuntimeException('Transaction not found');
            }
            if ($transaction['returned_at']) {
                throw new \RuntimeException('Book already returned');
            }

            $stmt = $this->pdo->prepare("UPDATE transactions SET returned_at = ? WHERE id = ?");
            $stmt->execute([date('Y-m-d H:i:s'), $transactionId]);
            
            $stmt = $this->pdo->prepare("UPDATE books SET status = 'available' WHERE id = ?");
            $stmt->execute([$transaction['book_id']]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getOverdueBooks(): array
    {
        $stmt = $this->pdo->query(
            "SELECT t.*, b.title, b.author, m.name as member_name 
             FROM transactions t 
             JOIN books b ON t.book_id = b.id 
             JOIN members m ON t.member_id = m.id 
             WHERE t.returned_at IS NULL AND t.due_date < date('now')
             ORDER BY t.due_date ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableBooks(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM books WHERE status = 'available' ORDER BY title");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
