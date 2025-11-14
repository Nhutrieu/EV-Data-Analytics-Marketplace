<?php
require_once __DIR__ . '/../db.php';

class AnalyticsController {
    private $pdo;

    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /** === 1. Tổng quan dữ liệu === */
    function overview() {
        return [
            "total_users"      => (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            "total_providers"  => (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role='provider'")->fetchColumn(),
            "total_consumers"  => (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role='consumer'")->fetchColumn(),
            "total_datasets"   => (int)$this->pdo->query("SELECT COUNT(*) FROM datasets")->fetchColumn(),
            "total_revenue"    => (float)$this->pdo->query("SELECT IFNULL(SUM(amount),0) FROM transactions")->fetchColumn(),
        ];
    }

    /** === 2. Phân tích dữ liệu theo datasets + giao dịch === */
    function data_trends() {

        // Top 10 datasets mua nhiều nhất
        $stmt = $this->pdo->query("
            SELECT d.id, d.title, COUNT(t.id) AS purchases
            FROM datasets d
            LEFT JOIN transactions t ON t.dataset_id = d.id
            GROUP BY d.id
            ORDER BY purchases DESC
            LIMIT 10
        ");
        $top_datasets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Doanh thu theo ngày
        $stmt2 = $this->pdo->query("
            SELECT DATE(created_at) AS day, SUM(amount) AS revenue
            FROM transactions
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ");
        $revenue_over_time = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // AI dự báo
        $forecast = $this->generate_forecast($revenue_over_time);

        return [
            "top_datasets"      => $top_datasets,
            "revenue_over_time" => $revenue_over_time,
            "forecast"          => $forecast
        ];
    }

    /** === 3. AI mô phỏng dự báo xu hướng === */
    private function generate_forecast($data) {

        // Không đủ dữ liệu → trả về dạng chuẩn
        if (count($data) < 2) {
            return [
                "trend" => "insufficient_data",
                "note"  => "Không đủ dữ liệu để phân tích xu hướng"
            ];
        }

        // Phân tích doanh thu 5 ngày cuối
        $values = array_column($data, "revenue");
        $last = array_slice($values, -5);
        $avg = array_sum($last) / count($last);

        $trend = ($last[count($last) - 1] > $avg) ? "up" : "down";

        return [
            "trend"   => $trend,
            "average" => $avg,
            "note"    => $trend === "up"
                ? "Doanh thu gần đây đang có xu hướng tăng."
                : "Doanh thu gần đây đang giảm nhẹ."
        ];
    }
}
