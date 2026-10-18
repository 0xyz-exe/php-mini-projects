<?php
declare(strict_types=1);

namespace ExpenseTracker\Tests;

use PHPUnit\Framework\TestCase;
use ExpenseTracker\Database;
use ExpenseTracker\ExpenseTracker;

class ExpenseTrackerTest extends TestCase
{
    private ExpenseTracker $tracker;
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/test_expenses_' . uniqid() . '.db';
        $db = new Database($this->dbPath);
        $this->tracker = new ExpenseTracker($db);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    public function testAddIncome(): void
    {
        $id = $this->tracker->addTransaction('income', 5000.00, 'Salary', '2026-10-01', 'Monthly salary');
        $this->assertGreaterThan(0, $id);
    }

    public function testAddExpense(): void
    {
        $id = $this->tracker->addTransaction('expense', 50.00, 'Food', '2026-10-02', 'Groceries');
        $this->assertGreaterThan(0, $id);
    }

    public function testInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tracker->addTransaction('invalid', 100.00, 'Test', '2026-10-01');
    }

    public function testNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tracker->addTransaction('expense', -50.00, 'Test', '2026-10-01');
    }

    public function testGetTransactionsByType(): void
    {
        $this->tracker->addTransaction('income', 5000.00, 'Salary', '2026-10-01');
        $this->tracker->addTransaction('expense', 50.00, 'Food', '2026-10-02');
        
        $income = $this->tracker->getTransactions('income');
        $this->assertCount(1, $income);
        
        $expenses = $this->tracker->getTransactions('expense');
        $this->assertCount(1, $expenses);
    }

    public function testMonthlySummary(): void
    {
        $this->tracker->addTransaction('income', 5000.00, 'Salary', '2026-10-01');
        $this->tracker->addTransaction('expense', 200.00, 'Food', '2026-10-05');
        $this->tracker->addTransaction('expense', 100.00, 'Transport', '2026-10-10');
        
        $summary = $this->tracker->getMonthlySummary('2026-10');
        $this->assertEquals(5000.00, $summary['income']);
        $this->assertEquals(300.00, $summary['expense']);
        $this->assertEquals(4700.00, $summary['balance']);
    }

    public function testCategoryBreakdown(): void
    {
        $this->tracker->addTransaction('expense', 200.00, 'Food', '2026-10-01');
        $this->tracker->addTransaction('expense', 150.00, 'Food', '2026-10-05');
        $this->tracker->addTransaction('expense', 100.00, 'Transport', '2026-10-10');
        
        $breakdown = $this->tracker->getCategoryBreakdown('2026-10');
        $this->assertCount(2, $breakdown);
        $this->assertEquals('Food', $breakdown[0]['category']);
        $this->assertEquals(350.00, $breakdown[0]['total']);
    }

    public function testExportToCSV(): void
    {
        $this->tracker->addTransaction('income', 5000.00, 'Salary', '2026-10-01');
        $csvPath = sys_get_temp_dir() . '/test_export_' . uniqid() . '.csv';
        
        $this->tracker->exportToCSV($csvPath);
        $this->assertFileExists($csvPath);
        
        $lines = file($csvPath);
        $this->assertCount(2, $lines); // Header + 1 transaction
        
        unlink($csvPath);
    }

    public function testDelete(): void
    {
        $id = $this->tracker->addTransaction('expense', 50.00, 'Food', '2026-10-01');
        $this->assertTrue($this->tracker->delete($id));
        $this->assertFalse($this->tracker->delete($id));
    }
}
