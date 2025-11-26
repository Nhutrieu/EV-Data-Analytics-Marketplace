<?php
// modules/DatasetModule.php

require_once __DIR__ . '/../classes/Dataset.php';

class DatasetModule
{
    private Dataset $dataset;

    /**
     * @param PDO $db
     * @param int $providerId ID provider đang đăng nhập
     */
    public function __construct(PDO $db, int $providerId)
    {
        // Provider bị giới hạn theo providerId
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
        return $this->dataset->create($data);
    }

    public function updateDataset(int $id, array $data): bool
    {
        return $this->dataset->update($id, $data);
    }

    public function deleteDataset(int $id): bool
    {
        return $this->dataset->delete($id);
    }

    public function updateDatasetFile(int $id, string $fileName, int $fileSize): bool
    {
        return $this->dataset->updateFile($id, $fileName, $fileSize);
    }
}
