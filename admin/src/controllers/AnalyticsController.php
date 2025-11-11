<?php
require_once __DIR__ . '/../db.php';

class AnalyticsController {
    private $pdo;

    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lấy tổng quan thống kê
    function overview() {
        $o = [];
        $o['total_users']      = (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $o['total_providers']  = (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role='provider'")->fetchColumn();
        $o['total_consumers']  = (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role='consumer'")->fetchColumn();
        $o['total_datasets']   = (int)$this->pdo->query("SELECT COUNT(*) FROM datasets")->fetchColumn();
        $o['total_revenue']    = (float)$this->pdo->query("SELECT IFNULL(SUM(amount),0) FROM transactions")->fetchColumn();

        $stmt = $this->pdo->query("
            SELECT d.id, d.title, COUNT(t.id) AS purchases
            FROM datasets d
            LEFT JOIN transactions t ON d.id = t.dataset_id
            GROUP BY d.id
            ORDER BY purchases DESC
            LIMIT 10
        ");
        $o['top_by_purchases'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt2 = $this->pdo->query("
            SELECT DATE(created_at) AS day, SUM(amount) AS total
            FROM transactions
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ");
        $o['revenue_by_day'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $o;
    }

    // Cập nhật doanh thu (chỉnh sửa thủ công)
    function updateRevenue($day, $amount) {
        $stmt = $this->pdo->prepare("UPDATE transactions SET amount=? WHERE DATE(created_at)=?");
        return $stmt->execute([$amount, $day]);
    }
}
?>
