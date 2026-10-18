# PDF and Document Utility

Document generation and processing utilities.

## Features

- Generate PDFs from HTML templates
- Generate reports from data
- Simple text-based output (demo)

## Setup

```bash
composer install
```

## Usage

```php
require 'vendor/autoload.php';

use DocumentUtility\PdfGenerator;

$pdf = new PdfGenerator();

// Generate from HTML
$html = '<h1>Report</h1><p>Content here...</p>';
$pdf->generateFromHtml($html, 'output.pdf');

// Generate report from data
$data = [
    ['Name' => 'Product A', 'Price' => '99.99', 'Stock' => '50'],
    ['Name' => 'Product B', 'Price' => '149.99', 'Stock' => '30']
];
$pdf->generateReport($data, 'report.pdf', 'Inventory Report');
```

## Limitations

- **Current implementation is text-based demo**
- Not real PDF format
- No styling/formatting
- No images
- No page breaks
- No OCR for scanned documents

## Real PDF Generation

For production, use maintained libraries:

```bash
composer require tecnickcom/tcpdf
# or
composer require setasign/fpdf
```

Then replace PdfGenerator with library calls.

## Security

- Input sanitization with htmlspecialchars
- Output encoding

## Production Requirements

- Integrate real PDF library (TCPDF/FPDF)
- Add template system
- Implement image support
- Add CSS styling
- Validate input documents
