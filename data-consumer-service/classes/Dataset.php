<?php
// consumer/src/Dataset.php

class Dataset
{
    private PDO $db;

    public function __construct(PDO $providerDb)
    {
        $this->db = $providerDb; // 👉 DB provider
    }

    /**
     * Lấy danh sách dataset đã publish + approved cho consumer
     */
    public function getPublicDatasets(): array
    {
        // Nếu bảng dùng cột "name" thì đổi d.title -> d.name
        $sql = "
            SELECT 
                d.id,
                d.name     AS name,
                d.type,
                d.price,
                d.price_unit,
                d.description,
                d.tags,
                d.file_name,
                d.file_size,
                d.created_at
            FROM datasets d
            WHERE d.status = 'published'
              AND d.admin_status = 'approved'
            ORDER BY d.created_at DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }
}
