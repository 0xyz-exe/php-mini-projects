<?php
declare(strict_types=1);

namespace TaskManager\Tests;

use PHPUnit\Framework\TestCase;
use TaskManager\Database;
use TaskManager\TaskManager;

class TaskManagerTest extends TestCase
{
    private TaskManager $manager;
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/test_tasks_' . uniqid() . '.db';
        $db = new Database($this->dbPath);
        $this->manager = new TaskManager($db);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }

    public function testCreateTask(): void
    {
        $id = $this->manager->create('Test task', 'Test description', 'high', '2026-12-31');
        $this->assertGreaterThan(0, $id);
    }

    public function testCreateTaskEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->manager->create('');
    }

    public function testCreateTaskInvalidPriority(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->manager->create('Test', null, 'invalid');
    }

    public function testListTasks(): void
    {
        $this->manager->create('Task 1');
        $this->manager->create('Task 2', null, 'high');
        
        $tasks = $this->manager->list();
        $this->assertCount(2, $tasks);
        
        $highTasks = $this->manager->list(null, 'high');
        $this->assertCount(1, $highTasks);
    }

    public function testCompleteTask(): void
    {
        $id = $this->manager->create('Complete me');
        $this->assertTrue($this->manager->complete($id));
        
        $tasks = $this->manager->list('completed');
        $this->assertCount(1, $tasks);
    }

    public function testDeleteTask(): void
    {
        $id = $this->manager->create('Delete me');
        $this->assertTrue($this->manager->delete($id));
        $this->assertFalse($this->manager->delete($id));
    }

    public function testUpdateTask(): void
    {
        $id = $this->manager->create('Original');
        $this->assertTrue($this->manager->update($id, 'Updated', 'New desc', 'high'));
        
        $tasks = $this->manager->list();
        $this->assertEquals('Updated', $tasks[0]['title']);
        $this->assertEquals('high', $tasks[0]['priority']);
    }
}
