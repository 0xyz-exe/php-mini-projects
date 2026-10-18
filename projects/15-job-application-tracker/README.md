# Job Application Tracker

Organize job search with status tracking and statistics.

## Features

- Track applications (company, position, date)
- Status tracking (applied, interview, offer, rejected)
- Interview dates
- Follow-up reminders
- Contact information
- Notes
- Search and filters
- Dashboard statistics
- CSV export

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use JobTracker\Database;
use JobTracker\JobTracker;

$db = new Database('jobs.db');
$tracker = new JobTracker($db);

// Add application
$id = $tracker->addApplication([
    'company' => 'Tech Corp',
    'position' => 'Senior Developer',
    'location' => 'Remote',
    'application_date' => '2026-10-01',
    'status' => 'applied',
    'salary_range' => '$100k-$120k',
    'job_url' => 'https://example.com/job/123',
    'notes' => 'Applied through LinkedIn'
]);

// Update status
$tracker->update($id, ['status' => 'interview', 'interview_date' => '2026-10-20']);

// Get applications by status
$interviews = $tracker->getApplications('interview');

// Search
$results = $tracker->getApplications(null, 'developer');

// Dashboard stats
$stats = $tracker->getStats();
echo "Total: " . $stats['total'] . "\n";
echo "Upcoming interviews: " . $stats['upcoming_interviews'] . "\n";

// Export
$tracker->exportCSV('applications.csv');
```

## Security

- PDO prepared statements
- Input validation
- Local storage only

## Limitations

- No reminders/notifications
- No calendar integration
- No document attachments
- No email tracking
- Local database only

## Privacy

Personal job search data stays local by default.
