<?php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/DatasetModule.php';

class DatasetController
{
    private DatasetModule $module;

    public function __construct()
    {
       session_start();

        if (empty($_SESSION['provider_id'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'Chưa đăng nhập provider'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $providerId = (int) $_SESSION['provider_id'];
        $db = Database::getConnection();
        $this->module = new DatasetModule($db, $providerId);
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

    // GET /api/datasets
    public function index(): void
    {
        $filters = [
            'type'   => $_GET['type']   ?? null,
            'status' => $_GET['status'] ?? null,
            'q'      => $_GET['q']      ?? null,
        ];

        $data = $this->module->listDatasets($filters);
        $this->json($data);
    }

    // GET /api/datasets/{id}
    public function show(int $id): void
    {
        $dataset = $this->module->getDataset($id);
        if (!$dataset) {
            $this->json(['message' => 'Dataset not found'], 404);
        }
        $this->json($dataset);
    }

    // POST /api/datasets
    // Body JSON: name, type, format, price, price_unit, description, tags, ...
    public function store(): void
    {
        try {
            $input = $this->getJsonInput();
            $id = $this->module->createDataset($input);

            $this->json([
                'id'      => $id,
                'message' => 'Dataset registered successfully',
            ], 201);
        } catch (InvalidArgumentException $e) {
            $this->json(['message' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->json(['message' => 'Server error', 'detail' => $e->getMessage()], 500);
        }
    }

    // PUT /api/datasets/{id}
    public function update(int $id): void
    {
        try {
            $input = $this->getJsonInput();
            $this->module->updateDataset($id, $input);
            $this->json(['message' => 'Dataset updated']);
        } catch (RuntimeException $e) {
            $this->json(['message' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            $this->json(['message' => 'Server error', 'detail' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/datasets/{id}
    public function destroy(int $id): void
    {
        try {
            $this->module->deleteDataset($id);
            $this->json(['message' => 'Dataset deleted']);
        } catch (RuntimeException $e) {
            $this->json(['message' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            $this->json(['message' => 'Server error', 'detail' => $e->getMessage()], 500);
        }
    }

    // POST /api/datasets/{id}/upload  (multipart/form-data)
    // field: file
public function upload(int $id): void
{
    try {
        if (empty($_FILES['file'])) {
            $this->json(['message' => 'No file uploaded, expected field "file"'], 400);
        }

        // 👇 Quan trọng: từ thư mục api/ nhảy lên root service, rồi vào /uploads
        $uploadDir = dirname(__DIR__) . '/uploads';

        $result = $this->module->uploadFile($id, $_FILES['file'], $uploadDir);

        $this->json([
            'message'   => 'File uploaded successfully',
            'file_name' => $result['file_name'],
            'file_size' => $result['file_size'],
        ]);
    } catch (RuntimeException $e) {
        $this->json(['message' => $e->getMessage()], 400);
    } catch (Throwable $e) {
        $this->json(['message' => 'Server error', 'detail' => $e->getMessage()], 500);
    }
}
}

