<?php
declare(strict_types=1);

namespace CsvTool;

class CsvProcessor
{
    private int $maxFileSize;

    public function __construct(int $maxFileSizeMB = 10)
    {
        $this->maxFileSize = $maxFileSizeMB * 1024 * 1024;
    }

    public function parse(string $filepath): array
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException('File not found');
        }
        
        if (filesize($filepath) > $this->maxFileSize) {
            throw new \RuntimeException('File exceeds size limit');
        }

        $fp = fopen($filepath, 'r');
        if ($fp === false) {
            throw new \RuntimeException('Cannot open file');
        }

        $data = [];
        $headers = fgetcsv($fp);
        
        while (($row = fgetcsv($fp)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        fclose($fp);
        return $data;
    }

    public function export(array $data, string $filepath, array $headers = []): void
    {
        $fp = fopen($filepath, 'w');
        if ($fp === false) {
            throw new \RuntimeException('Cannot create file');
        }

        if (empty($headers) && !empty($data)) {
            $headers = array_keys($data[0]);
        }

        if (!empty($headers)) {
            fputcsv($fp, $headers);
        }

        foreach ($data as $row) {
            $sanitized = array_map([$this, 'sanitizeFormulaInjection'], $row);
            fputcsv($fp, $sanitized);
        }

        fclose($fp);
    }

    private function sanitizeFormulaInjection($value): string
    {
        $value = (string) $value;
        if (strlen($value) > 0 && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $value;
        }
        return $value;
    }

    public function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($data as $index => $row) {
            foreach ($rules as $field => $rule) {
                if ($rule === 'required' && empty($row[$field])) {
                    $errors[] = "Row $index: $field is required";
                }
                
                if (isset($rule['type'])) {
                    if ($rule['type'] === 'email' && !filter_var($row[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Row $index: $field must be valid email";
                    }
                    if ($rule['type'] === 'numeric' && !is_numeric($row[$field])) {
                        $errors[] = "Row $index: $field must be numeric";
                    }
                }
            }
        }
        
        return $errors;
    }

    public function findDuplicates(array $data, string $field): array
    {
        $seen = [];
        $duplicates = [];
        
        foreach ($data as $index => $row) {
            $value = $row[$field] ?? null;
            if ($value !== null) {
                if (isset($seen[$value])) {
                    $duplicates[] = ['index' => $index, 'value' => $value, 'first_seen' => $seen[$value]];
                } else {
                    $seen[$value] = $index;
                }
            }
        }
        
        return $duplicates;
    }
}
