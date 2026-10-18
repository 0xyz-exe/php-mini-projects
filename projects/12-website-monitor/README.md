# Website Health Monitor

HTTP monitoring tool with status checks and response time tracking.

## Features

- HTTP/HTTPS status checks
- Response time measurement
- Timeout handling
- Multiple URL checking
- CSV reports
- Configurable timeouts

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use Monitor\WebsiteMonitor;

// Allow specific hosts only (SSRF protection)
$monitor = new WebsiteMonitor(10, ['example.com', 'api.example.com']);

// Check single URL
$result = $monitor->check('https://example.com');
echo "Status: " . $result['status'] . "\n";
echo "Response time: " . $result['response_time'] . "ms\n";

// Check multiple
$urls = [
    'https://example.com',
    'https://api.example.com/health'
];
$results = $monitor->checkMultiple($urls);

// Export report
$monitor->exportReport($results, 'monitor_report.csv');
```

## Scheduled Monitoring

### Windows (Task Scheduler)
```powershell
schtasks /create /tn "Website Monitor" /tr "php C:\path\to\monitor.php" /sc hourly
```

### Linux/Mac (cron)
```bash
0 * * * * php /path/to/monitor.php
```

## Security - SSRF Protection

- URL scheme validation (http/https only)
- Host whitelist support
- Timeout limits

## Limitations

- Basic SSRF protection (no IP validation)
- No loopback/private IP blocking
- No DNS rebinding protection
- No concurrent requests
- No alerting system

## Production Requirements

- Implement IP address validation
- Block private/loopback ranges (127.0.0.0/8, 10.0.0.0/8, 192.168.0.0/16, etc)
- Block link-local (169.254.0.0/16)
- Add DNS validation
- Implement alerting (email/webhook)
- Add retry logic
- Store history in database
