# File Upload Manager

Secure file upload with validation and storage management.

## Features

- File upload with validation
- Size limits (configurable)
- Type validation by extension
- Randomized storage filenames
- File listing
- Delete files
- Upload error handling

## Setup

```bash
composer install
mkdir uploads
```

## Usage

```php
require 'vendor/autoload.php';

use FileUpload\FileUploadManager;

$manager = new FileUploadManager('uploads', 5, ['jpg', 'png', 'pdf']);

// Handle upload
if (isset($_FILES['file'])) {
    try {
        $result = $manager->upload($_FILES['file']);
        echo "Uploaded: " . $result['filename'];
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// List files
$files = $manager->listFiles();

// Download
$path = $manager->getFilePath($filename);
if ($path) {
    header('Content-Type: application/octet-stream');
    readfile($path);
}

// Delete
$manager->delete($filename);
```

## Security

- Randomized filenames prevent overwriting
- Extension whitelist
- Size limits
- Upload error handling
- basename() prevents directory traversal

## Limitations

- No MIME type validation (extension only)
- No image dimension validation
- No virus scanning
- No per-user quotas
- No authorization checks
- Uploads inside web root (development only)

## Production Requirements

- Move uploads outside web root
- Add MIME validation with finfo_file()
- Implement authorization
- Add virus scanning
- Prevent script execution in upload dir (.htaccess/nginx config)
- Validate image dimensions for images
- Add per-user storage quotas
