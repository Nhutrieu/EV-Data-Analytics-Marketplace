<?php
// api/PricingController.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/PricingModule.php';

class PricingController
{
    private PricingModule $module;

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
        $this->module = new PricingModule($db, $providerId);
    }

    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public function show(): void
    {
        $policy = $this->module->getPolicy();
        $this->json($policy);
    }

    public function update(): void
    {
        try {
            $raw  = file_get_contents('php://input');
            $data = json_decode($raw, true) ?? [];
            $this->module->updatePolicy($data);

            $this->json([
                'success' => true,
                'message' => 'Đã lưu chính sách giá'
            ]);
        } catch (InvalidArgumentException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi server',
                'detail'  => $e->getMessage()
            ], 500);
        }
    }
}
