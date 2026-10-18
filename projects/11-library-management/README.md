# Library Management System

Book borrowing system with members, due dates, and overdue tracking.

## Features

- Book and member management
- Borrow and return operations
- Due dates (14 days default)
- Overdue tracking
- Availability checking
- Transaction-safe operations
- Prevent invalid state transitions

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Library\Database;
use Library\LibraryManager;

$db = new Database('library.db');
$library = new LibraryManager($db);

// Add book
$bookId = $library->addBook('1984', 'George Orwell', '978-0451524935', 'Fiction');

// Add member
$memberId = $library->addMember('John Doe', 'john@example.com');

// Borrow book
$transactionId = $library->borrowBook($bookId, $memberId, 14);

// Return book
$library->returnBook($transactionId);

// Check overdue
$overdue = $library->getOverdueBooks();

// Available books
$available = $library->getAvailableBooks();
```

## Security

- Database transactions for borrow/return
- State validation (can't borrow borrowed book)
- Email validation
- PDO prepared statements

## Limitations

- No fines calculation
- No renewals
- No reservations
- No multiple copies per title
- No barcode scanning
