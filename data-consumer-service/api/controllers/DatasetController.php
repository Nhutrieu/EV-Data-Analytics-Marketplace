<?php
// consumer/api/DatasetController.php
require_once __DIR__ . '/../../modules/DatasetModule.php';


class DatasetController
{
    private DatasetModule $module;

    public function __construct()
    {
        $this->module = new DatasetModule();
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $datasets = $this->module->listPublic();

            echo json_encode([
                'success' => true,
                'data'    => $datasets,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Không lấy được danh sách dataset',
                'error'   => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
