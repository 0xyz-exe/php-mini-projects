# CSV Import/Export Tool

CSV processing with validation, preview, and formula injection protection.

## Features

- CSV parsing
- CSV export with headers
- Column validation
- Duplicate detection
- Formula injection protection
- File size limits
- Data preview

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use CsvTool\CsvProcessor;

$processor = new CsvProcessor(10); // 10MB limit

// Parse CSV
$data = $processor->parse('data.csv');

// Validate
$rules = [
    'email' => ['type' => 'email'],
    'age' => ['type' => 'numeric'],
    'name' => 'required'
];
$errors = $processor->validate($data, $rules);

// Find duplicates
$duplicates = $processor->findDuplicates($data, 'email');

// Export
$processor->export($data, 'output.csv');
```

## Security

- Formula injection protection (prepends ' to =+-@\t\r)
- File size limits
- Input validation

## Limitations

- Memory-based (not streaming for large files)
- Basic validation rules
- No encoding detection
- No column mapping UI
- No transaction support

## Production Requirements

- Implement streaming for large files
- Add encoding detection/conversion
- Build preview UI with mapping
- Add transaction confirmation
- Implement batch processing
