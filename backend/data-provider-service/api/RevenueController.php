<?php
// api/RevenueController.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/RevenueModule.php';

class RevenueController
{
    private RevenueModule $module;
public function __construct()
{
    session_start();

    // Nếu chưa login provider thì chặn luôn
    if (empty($_SESSION['provider_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['message' => 'Chưa đăng nhập provider'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Provider hiện tại lấy từ session
    $providerId = (int) $_SESSION['provider_id'];

    $db = Database::getConnection();
    $this->module = new RevenueModule($db, $providerId);
}


    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Parse date với format Y-m-d, nếu sai format thì trả về null
     */
    private function parseDateOrNull(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if ($dt && $dt->format('Y-m-d') === $value) {
            return $value;
        }

        // Format không đúng => bỏ filter ngày này
        return null;
    }

    // GET index.php?page=revenue_api[&from=YYYY-MM-DD&to=YYYY-MM-DD]
    public function dashboard(): void
    {
        $rawFrom = $_GET['from'] ?? null;
        $rawTo   = $_GET['to']   ?? null;

        $from = $this->parseDateOrNull($rawFrom);
        $to   = $this->parseDateOrNull($rawTo);

        try {
            $data = $this->module->getDashboard($from, $to);
            $this->json($data);
        } catch (Throwable $e) {
            // Trong production có thể ẩn detail đi, chỉ log ở server.
            $this->json([
                'message' => 'Server error',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }
}
