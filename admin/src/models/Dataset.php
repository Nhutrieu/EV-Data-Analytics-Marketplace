<?php
// models/Dataset.php

class Dataset
{
    private PDO $db;
    private string $table = 'datasets';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Lấy tất cả datasets với filter (dùng chung được nếu cần)
     * $filters = [
     *   'admin_status' => 'pending'|'approved'|'rejected'|null,
     *   'status'       => 'draft'|'published'|...|null,
     *   'q'            => 'từ khoá tìm kiếm',
     * ]
     */
    public function all(array $filters = []): array
    {
        $sql = "SELECT d.*, u.name AS provider_name
                FROM {$this->table} d
                LEFT JOIN users u ON d.provider_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['admin_status'])) {
            $sql .= " AND d.admin_status = :admin_status";
            $params[':admin_status'] = $filters['admin_status'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (d.name LIKE :q OR d.description LIKE :q2)";
            $params[':q']  = '%' . $filters['q'] . '%';
            $params[':q2'] = '%' . $filters['q'] . '%';
        }

        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dùng riêng cho admin: list dataset đang chờ duyệt
     */
    public function findPending(): array
    {
        $sql = "SELECT d.*, u.name AS provider_name
                FROM {$this->table} d
                LEFT JOIN users u ON d.provider_id = u.id
                WHERE d.admin_status = 'pending'
                ORDER BY d.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy 1 dataset theo id (cho admin xem chi tiết)
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT d.*, u.name AS provider_name
                FROM {$this->table} d
                LEFT JOIN users u ON d.provider_id = u.id
                WHERE d.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Set trạng thái duyệt của admin:
     * - $adminStatus: 'approved' hoặc 'rejected'
     * - $adminNote: ghi chú / lý do
     * - $publish: nếu true và approved -> status = 'published'
     */
    public function setStatus(int $id, string $adminStatus, ?string $adminNote = null, bool $publish = false): bool
    {
        $allowed = ['approved', 'rejected'];
        if (!in_array($adminStatus, $allowed, true)) {
            throw new InvalidArgumentException("adminStatus không hợp lệ");
        }

        $fields = [
            "admin_status = :admin_status",
            "admin_note   = :admin_note",
        ];
        $params = [
            ':admin_status' => $adminStatus,
            ':admin_note'   => $adminNote,
            ':id'           => $id,
        ];

        // Nếu approve + publish thì cho dataset public
        if ($adminStatus === 'approved' && $publish) {
            $fields[] = "status = 'published'";
        }

        $sql = "UPDATE {$this->table}
                SET " . implode(', ', $fields) . "
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Tăng lượt download (dùng cho consumer sau này)
     */
    public function increaseDownloads(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET downloads = downloads + 1
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
