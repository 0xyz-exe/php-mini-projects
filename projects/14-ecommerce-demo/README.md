# Simple E-Commerce Store

Demo store with cart, checkout, and inventory management.

## Features

- Product catalog with search
- Categories
- Shopping cart (session-based)
- Checkout form
- Order creation with transactions
- Stock management
- Order history

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Ecommerce\Database;
use Ecommerce\StoreManager;

$db = new Database('store.db');
$store = new StoreManager($db);

// Add products (admin)
$store->addProduct('Laptop', 999.99, 50, '15-inch display', 'Electronics');

// Browse products
$products = $store->getProducts('Electronics');

// Search
$results = $store->getProducts(null, 'laptop');

// Create order
$items = [
    ['product_id' => 1, 'quantity' => 2],
    ['product_id' => 3, 'quantity' => 1]
];
$orderId = $store->createOrder('John Doe', 'john@example.com', $items);

// View order
$order = $store->getOrder($orderId);
```

## Security

- Email validation
- Database transactions for orders
- Stock validation before checkout
- PDO prepared statements
- Server-side validation required

## Payment

**SIMULATED ONLY** - No real payment processing.

## Limitations

- No authentication
- No real payment gateway
- No shipping calculation
- No tax calculation
- No cart persistence (session only)
- No product images
- No reviews/ratings
- No wishlist
- Server-side authorization needed

## Production Requirements

- Add authentication
- Integrate payment gateway (Stripe/PayPal)
- Implement CSRF protection
- Add shipping/tax calculation
- Implement proper cart persistence
- Add SSL/HTTPS
- Add rate limiting
- Implement proper authorization
