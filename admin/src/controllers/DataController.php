<?php
// controllers/DataController.php
require_once __DIR__ . '/../models/Dataset.php';
require_once __DIR__ . '/../helpers.php';

class DataController {
    private $pdo;
    private $model;

    function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new Dataset($pdo);
    }

    function listPending() {
        return $this->model->findPending();
    }

    function approve($id, $admin_id) {
        $this->model->setStatus($id, 'approved');
        $this->log($admin_id, "approve_dataset", "dataset:$id");
        return ['ok' => true];
    }

    function reject($id, $admin_id, $reason = null) {
        $this->model->setStatus($id, 'rejected');
        $this->log($admin_id, "reject_dataset", "dataset:$id reason:$reason");
        return ['ok' => true];
    }

    private function log($admin_id, $action, $details = null) {
        $stmt = $this->pdo->prepare("INSERT INTO logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
    }
}
