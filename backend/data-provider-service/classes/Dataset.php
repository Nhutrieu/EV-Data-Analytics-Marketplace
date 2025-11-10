<?php
// classes/Dataset.php

class Dataset
{
    private PDO $db;
    private string $table = 'datasets';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Lấy danh sách datasets với filter đơn giản
    public function all(array $filters = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (name LIKE :q OR description LIKE :q2)";
            $params[':q']  = '%' . $filters['q'] . '%';
            $params[':q2'] = '%' . $filters['q'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table}
                (name, type, format, price, price_unit, description,
                 status, admin_status, admin_note, tags, file_name, file_size)
                VALUES
                (:name, :type, :format, :price, :price_unit, :description,
                 :status, :admin_status, :admin_note, :tags, :file_name, :file_size)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'         => $data['name'],
            ':type'         => $data['type']         ?? null,
            ':format'       => $data['format']       ?? null,
            ':price'        => $data['price']        ?? 0,
            ':price_unit'   => $data['price_unit']   ?? null,
            ':description'  => $data['description']  ?? null,
            ':status'       => $data['status']       ?? 'draft',
            ':admin_status' => $data['admin_status'] ?? 'pending',
            ':admin_note'   => $data['admin_note']   ?? null,
            ':tags'         => $data['tags']         ?? null,
            ':file_name'    => $data['file_name']    ?? null,
            ':file_size'    => $data['file_size']    ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET
                    name         = :name,
                    type         = :type,
                    format       = :format,
                    price        = :price,
                    price_unit   = :price_unit,
                    description  = :description,
                    status       = :status,
                    admin_status = :admin_status,
                    admin_note   = :admin_note,
                    tags         = :tags,
                    file_name    = :file_name,
                    file_size    = :file_size
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name'         => $data['name']         ?? null,
            ':type'         => $data['type']         ?? null,
            ':format'       => $data['format']       ?? null,
            ':price'        => $data['price']        ?? 0,
            ':price_unit'   => $data['price_unit']   ?? null,
            ':description'  => $data['description']  ?? null,
            ':status'       => $data['status']       ?? 'draft',
            ':admin_status' => $data['admin_status'] ?? 'pending',
            ':admin_note'   => $data['admin_note']   ?? null,
            ':tags'         => $data['tags']         ?? null,
            ':file_name'    => $data['file_name']    ?? null,
            ':file_size'    => $data['file_size']    ?? null,
            ':id'           => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Cập nhật file gắn với dataset
    public function updateFile(int $id, string $fileName, float $fileSize): bool
    {
        $sql = "UPDATE {$this->table}
                SET file_name = :file_name,
                    file_size = :file_size
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':file_name' => $fileName,
            ':file_size' => $fileSize,
            ':id'        => $id,
        ]);
    }
}
