#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use TaskManager\Database;
use TaskManager\TaskManager;

$db = new Database(__DIR__ . '/../tasks.db');
$manager = new TaskManager($db);

$command = $argv[1] ?? 'help';

try {
    switch ($command) {
        case 'add':
            $title = $argv[2] ?? throw new InvalidArgumentException('Title required');
            $description = $argv[3] ?? null;
            $priority = $argv[4] ?? 'medium';
            $dueDate = $argv[5] ?? null;
            $id = $manager->create($title, $description, $priority, $dueDate);
            echo "Task #$id created\n";
            break;

        case 'list':
            $status = $argv[2] ?? null;
            $priority = $argv[3] ?? null;
            $tasks = $manager->list($status, $priority);
            foreach ($tasks as $task) {
                printf("#%d [%s] %s - %s (Due: %s)\n", 
                    $task['id'], 
                    $task['priority'], 
                    $task['title'], 
                    $task['status'],
                    $task['due_date'] ?? 'N/A'
                );
            }
            echo count($tasks) . " task(s) found\n";
            break;

        case 'complete':
            $id = (int)($argv[2] ?? throw new InvalidArgumentException('Task ID required'));
            if ($manager->complete($id)) {
                echo "Task #$id completed\n";
            } else {
                echo "Task #$id not found\n";
            }
            break;

        case 'delete':
            $id = (int)($argv[2] ?? throw new InvalidArgumentException('Task ID required'));
            if ($manager->delete($id)) {
                echo "Task #$id deleted\n";
            } else {
                echo "Task #$id not found\n";
            }
            break;

        case 'update':
            $id = (int)($argv[2] ?? throw new InvalidArgumentException('Task ID required'));
            $title = $argv[3] ?? null;
            $description = $argv[4] ?? null;
            $priority = $argv[5] ?? null;
            $dueDate = $argv[6] ?? null;
            if ($manager->update($id, $title, $description, $priority, $dueDate)) {
                echo "Task #$id updated\n";
            } else {
                echo "No changes made\n";
            }
            break;

        case 'help':
        default:
            echo "Task Manager CLI\n\n";
            echo "Commands:\n";
            echo "  add <title> [description] [priority] [due_date]\n";
            echo "  list [status] [priority]\n";
            echo "  complete <id>\n";
            echo "  delete <id>\n";
            echo "  update <id> [title] [description] [priority] [due_date]\n";
            echo "  help\n";
            break;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
