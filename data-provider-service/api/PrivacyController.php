<?php
// api/PrivacyController.php
//
// API cho Data Provider cấu hình bảo mật & ẩn danh:
//   - GET  /index.php?page=privacy_api   → lấy cài đặt hiện tại
//   - POST /index.php?page=privacy_api   → cập nhật cài đặt
//
// Frontend (privacy.html / privacy.js) sẽ gọi tới ?page=privacy_api

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/PrivacyModule.php';

class PrivacyController
{
    private PrivacyModule $module;

public function __construct()
{
    // Tạm thời bỏ check session, hard-code provider_id = 1 để test

    $providerId = 1; // ID provider bạn muốn giả lập (nhớ là phải tồn tại trong DB)
    $db = Database::getConnection();
    $this->module = new PrivacyModule($db, $providerId);
}


    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * GET /index.php?page=privacy_api
     * Trả về cài đặt bảo mật của provider hiện tại.
     */
    public function show(): void
    {
        try {
            $settings = $this->module->getSettings();
            $this->json($settings);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy cài đặt bảo mật',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /index.php?page=privacy_api
     * Body JSON: { anonymize, standard, retention_months, access_control }
     */
    public function update(): void
    {
        try {
            $data = $this->getJsonInput();
            $this->module->saveSettings($data);

            $this->json([
                'success' => true,
                'message' => 'Đã lưu cài đặt bảo mật.',
            ]);
        } catch (InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi server khi lưu cài đặt bảo mật',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }
}
