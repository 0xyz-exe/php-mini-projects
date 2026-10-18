<?php
declare(strict_types=1);

namespace JobTracker;

use PDO;

class JobTracker
{
    private PDO $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPdo();
    }

    public function addApplication(array $data): int
    {
        if (empty($data['company']) || empty($data['position'])) {
            throw new \InvalidArgumentException('Company and position required');
        }

        $fields = ['company', 'position', 'location', 'application_date', 'status', 'salary_range', 'job_url', 'contact_name', 'contact_email', 'notes', 'follow_up_date', 'interview_date'];
        $values = [];
        $placeholders = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $values[] = $data[$field];
                $placeholders[] = '?';
            } else {
                $values[] = null;
                $placeholders[] = '?';
            }
        }

        $sql = "INSERT INTO applications (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return (int) $this->pdo->lastInsertId();
    }

    public function getApplications(?string $status = null, ?string $search = null, string $sortBy = 'application_date', string $sortOrder = 'DESC'): array
    {
        $sql = "SELECT * FROM applications WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        if ($search) {
            $sql .= " AND (company LIKE ? OR position LIKE ? OR notes LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $allowedSort = ['application_date', 'company', 'position', 'status'];
        $sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'application_date';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY $sortBy $sortOrder";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool
    {
        $updates = [];
        $params = [];
        
        $allowedFields = ['company', 'position', 'location', 'status', 'salary_range', 'job_url', 'contact_name', 'contact_email', 'notes', 'follow_up_date', 'interview_date'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE applications SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM applications WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function getStats(): array
    {
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM applications GROUP BY status");
        $statsByStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM applications");
        $total = (int) $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM applications WHERE interview_date IS NOT NULL AND interview_date >= date('now')");
        $upcomingInterviews = (int) $stmt->fetchColumn();
        
        return [
            'total' => $total,
            'by_status' => $statsByStatus,
            'upcoming_interviews' => $upcomingInterviews
        ];
    }

    public function exportCSV(string $filename): void
    {
        $applications = $this->getApplications();
        
        $fp = fopen($filename, 'w');
        if ($fp === false) {
            throw new \RuntimeException("Cannot open file: $filename");
        }
        
        if (!empty($applications)) {
            fputcsv($fp, array_keys($applications[0]));
            foreach ($applications as $app) {
                fputcsv($fp, $app);
            }
        }
        
        fclose($fp);
    }
}
