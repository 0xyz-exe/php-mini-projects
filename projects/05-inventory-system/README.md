# Inventory Management System

Stock tracking with movements, low-stock alerts, and valuation.

## Features

- Product and category management
- Stock-in and stock-out with transactions
- Movement history
- Low-stock alerts
- Inventory valuation
- CSV export
- Negative stock prevention

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Inventory\Database;
use Inventory\InventoryManager;

$db = new Database('inventory.db');
$manager = new InventoryManager($db);

// Add product
$id = $manager->addProduct('Widget', 'Electronics', 29.99, 100, 20);

// Stock in
$manager->stockIn($id, 50, 'Received shipment');

// Stock out
$manager->stockOut($id, 10, 'Sold to customer');

// Low stock products
$lowStock = $manager->getLowStockProducts();

// Total inventory value
$value = $manager->getInventoryValue();

// Movement history
$movements = $manager->getMovements($id);

// Export CSV
$manager->exportCSV('inventory_report.csv');
```

## Testing

```bash
vendor/bin/phpunit
```

## Security

- Database transactions for stock changes
- Negative stock prevention
- PDO prepared statements
- Input validation

## Limitations

- No barcode scanning
- No multiple warehouses
- No supplier management
- No purchase orders
