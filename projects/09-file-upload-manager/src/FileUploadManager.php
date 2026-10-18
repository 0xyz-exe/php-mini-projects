<?php
declare(strict_types=1);

namespace FileUpload;

class FileUploadManager
{
    private string $uploadDir;
    private int $maxSize;
    private array $allowedTypes;

    public function __construct(string $uploadDir = 'uploads', int $maxSizeMB = 5, array $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'txt'])
    {
        $this->uploadDir = rtrim($uploadDir, '/');
        $this->maxSize = $maxSizeMB * 1024 * 1024;
        $this->allowedTypes = $allowedTypes;
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new \InvalidArgumentException('Invalid file upload');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException($this->getUploadErrorMessage($file['error']));
        }

        if ($file['size'] > $this->maxSize) {
            throw new \RuntimeException('File exceeds maximum size');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \RuntimeException('File type not allowed');
        }

        // ponytail: no MIME validation, add finfo_file() check when needed
        
        $safeFilename = $this->generateSafeFilename($extension);
        $destination = $this->uploadDir . '/' . $safeFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }

        return [
            'filename' => $safeFilename,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'path' => $destination
        ];
    }

    private function generateSafeFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    private function getUploadErrorMessage(int $error): string
    {
        return match($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
            default => 'Unknown upload error'
        };
    }

    public function listFiles(): array
    {
        $files = [];
        foreach (scandir($this->uploadDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $path = $this->uploadDir . '/' . $file;
            if (is_file($path)) {
                $files[] = [
                    'filename' => $file,
                    'size' => filesize($path),
                    'uploaded_at' => date('Y-m-d H:i:s', filemtime($path))
                ];
            }
        }
        return $files;
    }

    public function delete(string $filename): bool
    {
        $path = $this->uploadDir . '/' . basename($filename);
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    public function getFilePath(string $filename): ?string
    {
        $path = $this->uploadDir . '/' . basename($filename);
        return file_exists($path) ? $path : null;
    }
}
