# Expense Tracker

Personal finance application for tracking income and expenses.

## Features

- Income and expense records
- Categories and descriptions
- Monthly summaries
- Category breakdowns
- Date filtering
- CSV export
- SQLite persistence

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use ExpenseTracker\Database;
use ExpenseTracker\ExpenseTracker;

$db = new Database('my_expenses.db');
$tracker = new ExpenseTracker($db);

// Add income
$tracker->addTransaction('income', 5000.00, 'Salary', '2026-10-01', 'Monthly salary');

// Add expense
$tracker->addTransaction('expense', 200.00, 'Food', '2026-10-05', 'Groceries');

// Get monthly summary
$summary = $tracker->getMonthlySummary('2026-10');
echo "Balance: $" . $summary['balance'] . "\n";

// Category breakdown
$breakdown = $tracker->getCategoryBreakdown('2026-10');

// Export to CSV
$tracker->exportToCSV('expenses_export.csv');
```

## Testing

```bash
vendor/bin/phpunit
```

## Limitations

- Local database only
- No multi-user support
- No bank integration
- Manual entry required
