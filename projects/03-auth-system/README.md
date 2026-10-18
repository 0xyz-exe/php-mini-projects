# Secure Authentication System

Reusable authentication module with security best practices.

## Features

- User registration with email validation
- Secure password hashing (bcrypt)
- Login with verification
- Rate limiting (5 attempts per 15 minutes)
- Session management
- CSRF token generation and validation
- Secure cookie settings
- Generic error messages

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Auth\Database;
use Auth\Auth;

$db = new Database('auth.db');
$auth = new Auth($db);

// Register
try {
    $auth->register('user@example.com', 'secure_password123');
} catch (Exception $e) {
    echo "Registration failed: " . $e->getMessage();
}

// Login
$userId = $auth->login('user@example.com', 'secure_password123', $_SERVER['REMOTE_ADDR']);

if ($userId) {
    $auth->startSession($userId);
    echo "Login successful!";
} else {
    echo "Invalid credentials";
}

// Check if logged in
if ($auth->isLoggedIn()) {
    $userId = $auth->getUserId();
}

// CSRF protection
$csrfToken = $auth->generateCsrfToken();
// In form: <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

// Validate CSRF on form submit
if ($auth->validateCsrfToken($_POST['csrf_token'])) {
    // Process form
}

// Logout
$auth->logout();
```

## Security Features

- `password_hash()` with bcrypt (cost 10)
- `password_verify()` for constant-time comparison
- Rate limiting by email and IP
- Session regeneration on login
- HttpOnly and SameSite cookies
- CSRF tokens with `hash_equals()`
- Generic authentication error messages

## Testing

```bash
vendor/bin/phpunit
```

## Limitations

- Single database file
- No password reset flow
- No email verification
- No 2FA
- Rate limiting clears on successful login
- Requires HTTPS in production
- No account recovery mechanism
