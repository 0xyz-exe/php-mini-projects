<?php
declare(strict_types=1);

namespace Monitor;

class WebsiteMonitor
{
    private int $timeoutSeconds;
    private array $allowedHosts;

    public function __construct(int $timeoutSeconds = 10, array $allowedHosts = [])
    {
        $this->timeoutSeconds = $timeoutSeconds;
        $this->allowedHosts = $allowedHosts;
    }

    public function check(string $url): array
    {
        if (!$this->isAllowedUrl($url)) {
            throw new \InvalidArgumentException('URL not in allowed list or invalid');
        }

        $startTime = microtime(true);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeoutSeconds,
                'method' => 'GET',
                'user_agent' => 'PHP Website Monitor/1.0'
            ]
        ]);

        $result = [
            'url' => $url,
            'checked_at' => date('Y-m-d H:i:s'),
            'status' => null,
            'response_time' => null,
            'error' => null
        ];

        try {
            $response = @file_get_contents($url, false, $context);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response === false) {
                $result['status'] = 'error';
                $result['error'] = 'Failed to fetch URL';
            } else {
                // Parse HTTP response code from headers
                $statusCode = 200;
                if (isset($http_response_header[0])) {
                    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
                    $statusCode = (int)($matches[1] ?? 200);
                }
                
                $result['status'] = $statusCode;
                $result['response_time'] = $responseTime;
            }
        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);
        
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }
        
        if (!in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        // ponytail: basic SSRF protection, add IP validation for production
        if (empty($this->allowedHosts)) {
            return true; // Allow all if no whitelist
        }

        return in_array($parsed['host'], $this->allowedHosts);
    }

    public function checkMultiple(array $urls): array
    {
        $results = [];
        foreach ($urls as $url) {
            try {
                $results[] = $this->check($url);
            } catch (\Exception $e) {
                $results[] = [
                    'url' => $url,
                    'checked_at' => date('Y-m-d H:i:s'),
                    'status' => 'error',
                    'response_time' => null,
                    'error' => $e->getMessage()
                ];
            }
        }
        return $results;
    }

    public function exportReport(array $results, string $filename): void
    {
        $fp = fopen($filename, 'w');
        if ($fp === false) {
            throw new \RuntimeException("Cannot open file: $filename");
        }

        fputcsv($fp, ['URL', 'Status', 'Response Time (ms)', 'Checked At', 'Error']);
        
        foreach ($results as $r) {
            fputcsv($fp, [
                $r['url'],
                $r['status'],
                $r['response_time'] ?? 'N/A',
                $r['checked_at'],
                $r['error'] ?? ''
            ]);
        }
        
        fclose($fp);
    }
}
