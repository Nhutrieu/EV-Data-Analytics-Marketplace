<?php
// api/PricingController.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/PricingModule.php';

class PricingController
{
   private PricingModule $module;

public function __construct()
{
    // Tạm thời bỏ check session, hard-code provider_id = 1 để test

    $providerId = 1; // ID provider bạn muốn giả lập (nhớ là phải tồn tại trong DB)
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
