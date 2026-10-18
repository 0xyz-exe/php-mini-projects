# PHP Mini Projects

15+ complete, functional PHP applications demonstrating modern PHP development, backend logic, database integration, authentication, REST APIs, and secure web development.

## Technology Stack

- **PHP**: 8.3.33
- **Database**: SQLite (default), MySQL/MariaDB (optional)
- **Standards**: PSR-12, PSR-4
- **Tools**: Composer, PHPUnit

## Prerequisites

- PHP 8.3+ with extensions: `openssl`, `mbstring`, `curl`, `pdo_sqlite`
- Composer
- Git

## Projects

### 01. CLI Task Manager
Command-line task management with priorities, due dates, and JSON/SQLite storage.

### 02. Expense Tracker
Personal finance tracker with categories, summaries, and CSV export.

### 03. Secure Authentication System
Reusable auth module with bcrypt, CSRF protection, and rate limiting.

### 04. CRUD Admin Dashboard
Administrative dashboard with pagination, search, and form validation.

### 05. Inventory Management System
Stock tracking with low-stock alerts, movement history, and reports.

### 06. REST API
JSON REST API with CRUD endpoints, validation, and pagination.

### 07. URL Shortener
URL shortening service with click tracking and validation.

### 08. Blog CMS
Lightweight content management with categories, tags, and SEO.

### 09. File Upload Manager
Secure file upload with type validation and storage management.

### 10. CSV Import/Export Tool
CSV processing with validation, preview, and bulk operations.

### 11. Library Management System
Book borrowing system with members, due dates, and reporting.

### 12. Website Health Monitor
HTTP monitoring tool with status checks and response times.

### 13. PDF Document Utility
PDF generation and document processing utilities.

### 14. Simple E-Commerce Store
Demo store with cart, checkout, and inventory management.

### 15. Job Application Tracker
Job search organizer with status tracking and statistics.

## Quick Start

```bash
# Clone repository
git clone <repo-url>
cd php-mini-projects

# Navigate to a project
cd projects/01-cli-task-manager

# Install dependencies
composer install

# Run the application
php bin/task.php --help
```

## Testing

```bash
# Run tests for a specific project
cd projects/01-cli-task-manager
composer test

# Or use PHPUnit directly
vendor/bin/phpunit
```

## Security Considerations

- All passwords use `password_hash()` with bcrypt
- Database queries use PDO prepared statements
- CSRF tokens protect state-changing forms
- File uploads validate types and sanitize names
- Output escaping prevents XSS
- Input validation on server side
- Secure session configuration

## Limitations

- Projects use PHP's built-in server for development only
- SQLite suitable for demos, use MySQL/PostgreSQL for production
- SSRF protections documented but require network-level controls
- File upload projects need web root isolation in production
- Authentication projects require HTTPS in production
- No real payment processing in e-commerce demo

## License

MIT License - See LICENSE file

## Contributing

Pull requests welcome. Ensure code follows PSR-12 and includes tests.
