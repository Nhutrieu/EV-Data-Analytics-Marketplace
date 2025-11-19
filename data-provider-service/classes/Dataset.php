<?php
// classes/Dataset.php

class Dataset
{
    private PDO $db;
    private string $table = 'datasets';
    private ?int $providerId;

    /**
     * @param PDO $db
     * @param int|null $providerId  null = không filter theo provider (để sau này dùng cho admin/consumer)
     */
    public function __construct(PDO $db, ?int $providerId = null)
    {
        $this->db = $db;
        $this->providerId = $providerId;
    }

    /**
     * Lấy danh sách datasets với filter đơn giản
     * $filters = [
     *   'type'         => ?string,
     *   'status'       => ?string,
     *   'admin_status' => ?string,
     *   'q'            => ?string,
     * ]
     */
    public function all(array $filters = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Giới hạn theo provider nếu có
        if ($this->providerId !== null) {
            $sql .= " AND provider_id = :provider_id";
            $params[':provider_id'] = $this->providerId;
        }

        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['admin_status'])) {
            $sql .= " AND admin_status = :admin_status";
            $params[':admin_status'] = $filters['admin_status'];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (name LIKE :q OR description LIKE :q2)";
            $params[':q']  = '%' . $filters['q'] . '%';
            $params[':q2'] = '%' . $filters['q'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm 1 dataset theo id (có xét provider nếu có)
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];

        if ($this->providerId !== null) {
            $sql .= " AND provider_id = :provider_id";
            $params[':provider_id'] = $this->providerId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Tạo dataset mới
     * $data gồm các field:
     *  name, type, format, price, price_unit, description,
     *  status, admin_status, admin_note, tags, file_name, file_size
     */
    public function create(array $data): int
{
    $sql = "INSERT INTO {$this->table}
            (provider_id, name, type, format, price, price_unit, description,
             status, admin_status, admin_note, downloads, tags, file_name, file_size)
            VALUES
            (:provider_id, :name, :type, :format, :price, :price_unit, :description,
             :status, :admin_status, :admin_note, :downloads, :tags, :file_name, :file_size)";

    if ($this->providerId === null) {
        throw new RuntimeException("providerId is required when creating dataset từ phía provider");
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':provider_id'  => $this->providerId,
        ':name'         => $data['name'],
        ':type'         => $data['type']         ?? null,
        ':format'       => $data['format']       ?? null,
        ':price'        => $data['price']        ?? 0,
        ':price_unit'   => $data['price_unit']   ?? 'per-download',
        ':description'  => $data['description']  ?? null,
        ':status'       => $data['status']       ?? 'draft',
        ':admin_status' => $data['admin_status'] ?? 'pending',
        ':admin_note'   => $data['admin_note']   ?? null,
        ':downloads'    => $data['downloads']    ?? 0,      // 👈 thêm cột downloads
        ':tags'         => $data['tags']         ?? null,
        ':file_name'    => $data['file_name']    ?? null,
        ':file_size'    => $data['file_size']    ?? null,
    ]);

    return (int) $this->db->lastInsertId();
}

    /**
     * Cập nhật dataset
     */
    public function update(int $id, array $data): bool
    {
        // Whitelist field cho phép update
        $allowed = [
            'name',
            'type',
            'format',
            'price',
            'price_unit',
            'description',
            'status',
            'admin_status',
            'admin_note',
            'tags',
            'file_name',
            'file_size',
        ];

        $setParts = [];
        $params   = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($setParts)) {
            // Không có gì để update
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $params[':id'] = $id;

        if ($this->providerId !== null) {
            $sql .= " AND provider_id = :provider_id";
            $params[':provider_id'] = $this->providerId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Xoá dataset
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];

        if ($this->providerId !== null) {
            $sql .= " AND provider_id = :provider_id";
            $params[':provider_id'] = $this->providerId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Cập nhật thông tin file (sau khi upload thành công)
     */
    public function updateFile(int $id, string $fileName, int $fileSize): bool
    {
        $sql = "UPDATE {$this->table}
                SET file_name = :file_name,
                    file_size = :file_size
                WHERE id = :id";

        $params = [
            ':file_name' => $fileName,
            ':file_size' => $fileSize,
            ':id'        => $id,
        ];

        if ($this->providerId !== null) {
            $sql .= " AND provider_id = :provider_id";
            $params[':provider_id'] = $this->providerId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
