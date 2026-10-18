# CRUD Admin Dashboard

Product management dashboard with CRUD operations, pagination, and search.

## Features

- Create, read, update, delete products
- Pagination
- Search by name/description
- Form validation
- CSRF protection in web interface
- Responsive design

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Dashboard\Database;
use Dashboard\ProductManager;

$db = new Database('dashboard.db');
$manager = new ProductManager($db);

// Create product
$id = $manager->create('Laptop', 999.99, 50, '15-inch display');

// Get all with pagination
$products = $manager->getAll(1, 20);

// Search
$results = $manager->getAll(1, 20, 'laptop');

// Get by ID
$product = $manager->getById($id);

// Update
$manager->update($id, 'Gaming Laptop', 1299.99, 30, 'RGB keyboard');

// Delete
$manager->delete($id);
```

## Web Interface

See `public/index.php` for basic web interface with CSRF protection.

Start dev server:
```bash
php -S localhost:8000 -t public
```

## Testing

```bash
vendor/bin/phpunit
```

## Security

- PDO prepared statements
- Input validation
- CSRF tokens (in web interface)
- Output escaping (htmlspecialchars)

## Limitations

- No authentication
- No file uploads
- Basic styling
- SQLite only
