<?php
// api/SettingsController.php
//
// API cho Data Provider cài đặt tài khoản & hệ thống:
//   - GET  /backend/data-provider-service/index.php?page=settings_api
//   - POST /backend/data-provider-service/index.php?page=settings_api&section=company|password|system
//
// section:
//   - company  → form Thông tin Công ty
//   - password → form Đổi mật khẩu
//   - system   → form Cài đặt Hệ thống

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/SettingsModule.php';

class SettingsController
{
 private SettingsModule $module;

public function __construct()
{
    // Tạm thời bỏ check session, hard-code provider_id = 1 để test

    $providerId = 1; // ID provider bạn muốn giả lập (nhớ là phải tồn tại trong DB)
    $db = Database::getConnection();
    $this->module = new SettingsModule($db, $providerId);
}


    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
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
     * GET /index.php?page=settings_api
     */
    public function show(): void
    {
        try {
            $data = $this->module->getAll();
            $this->json($data);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy cài đặt.',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /index.php?page=settings_api&section=company|password|system
     */
    public function update(): void
    {
        $section = $_GET['section'] ?? '';

        try {
            $data = $this->getJsonInput();

            switch ($section) {
                case 'company':
                    $this->module->saveCompany($data);
                    $this->json([
                        'success' => true,
                        'message' => 'Đã cập nhật thông tin công ty.',
                    ]);
                    break;

                case 'password':
                    $this->module->changePassword($data);
                    $this->json([
                        'success' => true,
                        'message' => 'Đã đổi mật khẩu thành công.',
                    ]);
                    break;

                case 'system':
                    $this->module->saveSystem($data);
                    $this->json([
                        'success' => true,
                        'message' => 'Đã lưu cài đặt hệ thống.',
                    ]);
                    break;

                default:
                    $this->json([
                        'success' => false,
                        'message' => 'Section không hợp lệ (company | password | system).',
                    ], 400);
            }
        } catch (InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Lỗi server khi lưu cài đặt.',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }
}
