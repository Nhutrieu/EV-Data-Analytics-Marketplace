<?php
// controllers/DataController.php
require_once __DIR__ . '/../models/Dataset.php';
require_once __DIR__ . '/../helpers.php';

class DataController {
    private PDO $pdo;
    private Dataset $model;

    public function __construct(PDO $pdo) {
        $this->pdo   = $pdo;
        $this->model = new Dataset($pdo);
    }

    /**
     * Lấy danh sách dataset đang chờ duyệt
     */
    public function listPending(): array {
        return $this->model->findPending();
    }

    /**
     * Lấy 1 dataset theo id cho admin xem chi tiết
     */
    public function show(int $id): ?array {
        return $this->model->findById($id);
    }

    /**
     * Admin approve dataset
     * - $admin_id: lấy từ session admin login
     * - $note: ghi chú (optional)
     * - $publish: có public luôn cho consumer không? (default: true)
     */
    public function approve(int $id, int $admin_id, ?string $note = null, bool $publish = true): array {
        $ok = $this->model->setStatus($id, 'approved', $note, $publish);
        if (!$ok) {
            return ['ok' => false, 'message' => 'Không cập nhật được trạng thái dataset'];
        }

        $this->log($admin_id, "approve_dataset", "dataset:$id note:$note publish:" . ($publish ? '1' : '0'));
        return ['ok' => true];
    }

    /**
     * Admin reject dataset
     */
    public function reject(int $id, int $admin_id, ?string $reason = null): array {
        $ok = $this->model->setStatus($id, 'rejected', $reason, false);
        if (!$ok) {
            return ['ok' => false, 'message' => 'Không cập nhật được trạng thái dataset'];
        }

        $this->log($admin_id, "reject_dataset", "dataset:$id reason:$reason");
        return ['ok' => true];
    }

    /**
     * Ghi log hành động admin (bảng logs)
     */
    private function log(int $admin_id, string $action, ?string $details = null): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO logs (admin_id, action, details, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $admin_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
