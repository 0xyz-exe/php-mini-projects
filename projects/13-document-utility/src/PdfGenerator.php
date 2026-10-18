<?php
declare(strict_types=1);

namespace DocumentUtility;

class PdfGenerator
{
    public function generateFromHtml(string $html, string $outputPath): bool
    {
        // ponytail: basic HTML to text conversion, add TCPDF/FPDF for real PDF generation
        
        $text = strip_tags($html);
        $text = html_entity_decode($text);
        
        // Simple PDF-like structure (actually plain text for demo)
        $content = "%PDF-1.4\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= $text;
        
        return file_put_contents($outputPath, $content) !== false;
    }

    public function generateReport(array $data, string $outputPath, string $title = 'Report'): bool
    {
        $html = "<html><head><title>$title</title></head><body>";
        $html .= "<h1>" . htmlspecialchars($title) . "</h1>";
        $html .= "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";
        
        if (!empty($data)) {
            $html .= "<table border='1'>";
            $html .= "<tr>";
            foreach (array_keys($data[0]) as $key) {
                $html .= "<th>" . htmlspecialchars($key) . "</th>";
            }
            $html .= "</tr>";
            
            foreach ($data as $row) {
                $html .= "<tr>";
                foreach ($row as $value) {
                    $html .= "<td>" . htmlspecialchars($value) . "</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</table>";
        }
        
        $html .= "</body></html>";
        
        return $this->generateFromHtml($html, $outputPath);
    }
}
