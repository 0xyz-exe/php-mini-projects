# CLI Task Manager

Command-line task management with SQLite storage.

## Features

- Create, edit, delete, complete tasks
- Priorities (low, medium, high)
- Due dates
- Status filters (pending, completed)
- Persistent SQLite storage
- Input validation

## Setup

```bash
composer install
```

## Usage

```bash
# Add task
php bin/task.php add "Buy groceries" "Milk and eggs" high "2026-10-20"

# List all tasks
php bin/task.php list

# List by status
php bin/task.php list pending

# List by priority
php bin/task.php list "" high

# Complete task
php bin/task.php complete 1

# Update task
php bin/task.php update 1 "New title" "New description" medium

# Delete task
php bin/task.php delete 1

# Help
php bin/task.php help
```

## Testing

```bash
composer test
# or
vendor/bin/phpunit
```

## Database

SQLite database created at `tasks.db` on first run.

## Limitations

- CLI only (no web interface)
- No user authentication
- Single database file
