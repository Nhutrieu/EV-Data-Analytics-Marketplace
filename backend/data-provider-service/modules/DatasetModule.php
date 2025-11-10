<?php
// modules/DatasetModule.php

require_once __DIR__ . '/../classes/Dataset.php';

class DatasetModule
{
    private Dataset $dataset;

    public function __construct(PDO $db, int $providerId)
    {
        $this->dataset = new Dataset($db, $providerId);
    }

    public function listDatasets(array $filters = []): array
    {
        return $this->dataset->all($filters);
    }

    public function getDataset(int $id): ?array
    {
        return $this->dataset->find($id);
    }

    public function createDataset(array $data): int
    {
        if (empty($data['name']) || empty($data['type'])) {
            throw new InvalidArgumentException('Fields "name" và "type" là bắt buộc');
        }
        return $this->dataset->create($data);
    }

    public function updateDataset(int $id, array $data): bool
    {
        $existing = $this->dataset->find($id);
        if (!$existing) {
            throw new RuntimeException('Dataset not found');
        }

        return $this->dataset->update($id, $data);
    }

    public function deleteDataset(int $id): bool
    {
        $existing = $this->dataset->find($id);
        if (!$existing) {
            throw new RuntimeException('Dataset not found');
        }

        return $this->dataset->delete($id);
    }

    public function uploadFile(int $datasetId, array $file, string $uploadDir): array
    {
        $existing = $this->dataset->find($datasetId);
        if (!$existing) {
            throw new RuntimeException('Dataset not found');
        }

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error');
        }

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                throw new RuntimeException('Cannot create upload directory');
            }
        }

        $originalName = basename($file['name']);
        $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $originalName);
        $targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Cannot move uploaded file');
        }

        $fileSize = filesize($targetPath) ?: 0;

        $this->dataset->updateFile($datasetId, $safeName, (float)$fileSize);

        return [
            'file_name' => $safeName,
            'file_size' => $fileSize,
            'path'      => $targetPath,
        ];
    }
}
