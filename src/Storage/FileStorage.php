<?php

namespace Maharlika\Debugbar\Storage;

class FileStorage
{
    protected string $path;
    protected int $maxFiles;

    public function __construct(string $path, int $maxFiles = 20)
    {
        $this->path = $path;
        $this->maxFiles = $maxFiles;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function save(string $id, array $data): void
    {
        $filename = $this->getFilename($id);
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        $this->cleanup();
    }

    public function get(string $id): ?array
    {
        $filename = $this->getFilename($id);
        
        if (!file_exists($filename)) {
            return null;
        }

        $content = file_get_contents($filename);
        return json_decode($content, true);
    }

    public function all(): array
    {
        $files = glob($this->path . '/debugbar_*.json');
        rsort($files); // Most recent first

        $data = [];
        foreach ($files as $file) {
            $id = str_replace(
                [$this->path . '/debugbar_', '.json'],
                '',
                $file
            );
            $data[$id] = json_decode(file_get_contents($file), true);
        }

        return $data;
    }

    public function clear(): void
    {
        $files = glob($this->path . '/debugbar_*.json');
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    protected function getFilename(string $id): string
    {
        return $this->path . '/debugbar_' . $id . '.json';
    }

    protected function cleanup(): void
    {
        $files = glob($this->path . '/debugbar_*.json');
        
        if (count($files) <= $this->maxFiles) {
            return;
        }

        // Sort by modification time, oldest first
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Delete oldest files
        $filesToDelete = array_slice($files, 0, count($files) - $this->maxFiles);
        foreach ($filesToDelete as $file) {
            @unlink($file);
        }
    }
}