# URL Shortener

Local URL shortening with click tracking and validation.

## Features

- Generate short codes
- Custom short codes
- Click tracking
- URL validation (http/https only)
- Statistics
- Collision handling

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use UrlShortener\Database;
use UrlShortener\UrlShortener;

$db = new Database('urls.db');
$shortener = new UrlShortener($db);

// Shorten URL
$code = $shortener->shorten('https://example.com/very/long/url');
echo "Short code: $code\n";

// Custom code
$code = $shortener->shorten('https://example.com', 'custom');

// Resolve
$originalUrl = $shortener->resolve($code);
if ($originalUrl) {
    header("Location: $originalUrl");
    exit;
}

// Stats
$stats = $shortener->getStats($code);
echo "Clicks: " . $stats['clicks'] . "\n";

// Delete
$shortener->delete($code);
```

## Security

- Validates URL schemes (http/https only)
- Rejects javascript:, data:, file: schemes
- Prepared statements
- Unique short code constraints

## Limitations

- No authentication
- No expiration dates
- No rate limiting
- No QR codes
- Local only (not public deployment ready)
- No SSRF protection for destination fetching (doesn't fetch destinations)

## Production Requirements

Before public deployment:
- Add authentication
- Implement rate limiting
- Add expiration/TTL
- Monitor for abuse
- Consider destination safety checks
