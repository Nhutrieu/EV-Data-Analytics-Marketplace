<?php
// api/DatasetController.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../modules/DatasetModule.php';

class DatasetController
{
    private DatasetModule $module;

public function __construct()
{
    // Tạm thời bỏ check session, hard-code provider_id = 1 để test

    $providerId = 1; // ID provider bạn muốn giả lập (nhớ là phải tồn tại trong DB)
    $db = Database::getConnection();
    $this->module = new DatasetModule($db, $providerId);
}


    /**
     * GET /index.php?page=datasets
     */
    public function index(): void
    {
        $filters = [
            'type'         => $_GET['type']         ?? null,
            'status'       => $_GET['status']       ?? null,
            'admin_status' => $_GET['admin_status'] ?? null,
            'q'            => $_GET['q']            ?? null,
        ];

        $datasets = $this->module->listDatasets($filters);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datasets, JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /index.php?page=datasets&id=123
     */
    public function show(int $id): void
    {
        $dataset = $this->module->getDataset($id);

        if (!$dataset) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'Dataset không tồn tại'], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dataset, JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /index.php?page=datasets
     * Body JSON
     */
    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['message' => 'Body không phải JSON hợp lệ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $name       = trim($input['name'] ?? '');
        $type       = trim($input['type'] ?? '');
        $format     = trim($input['format'] ?? '');
        $price      = $input['price'] ?? null;
        $priceUnit  = trim($input['price_unit'] ?? '');
        $description = $input['description'] ?? null;
        $status      = $input['status']       ?? 'draft';
        $adminStatus = $input['admin_status'] ?? 'pending';
        $tags        = $input['tags']         ?? null;

        if ($name === '' || $type === '' || $format === '' || $price === null || $priceUnit === '') {
            http_response_code(422);
            echo json_encode(['message' => 'Thiếu dữ liệu bắt buộc'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = [
            'name'         => $name,
            'type'         => $type,
            'format'       => $format,
            'price'        => (float) $price,
            'price_unit'   => $priceUnit,
            'description'  => $description,
            'status'       => $status,
            'admin_status' => $adminStatus,
            'admin_note'   => $input['admin_note'] ?? null,
            'tags'         => is_array($tags) ? implode(',', $tags) : $tags,
        ];

        try {
            $id = $this->module->createDataset($data);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Không tạo được dataset', 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            return;
        }

        http_response_code(201);
        echo json_encode([
            'message' => 'Tạo dataset thành công',
            'id'      => $id,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /index.php?page=datasets&id=123
     */
    public function update(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['message' => 'Body không phải JSON hợp lệ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Không bắt buộc field nào, chỉ update field truyền lên
        $ok = $this->module->updateDataset($id, $input);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['message' => 'Dataset không tồn tại hoặc không có gì để cập nhật'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(['message' => 'Cập nhật thành công'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * DELETE /index.php?page=datasets&id=123
     */
    public function destroy(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $ok = $this->module->deleteDataset($id);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['message' => 'Dataset không tồn tại'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(['message' => 'Xoá dataset thành công'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /index.php?page=datasets&id=123&action=upload
     * multipart/form-data: file
     */
    public function upload(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['message' => 'File upload không hợp lệ'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $file      = $_FILES['file'];
        $fileName  = $file['name'];
        $tmpName   = $file['tmp_name'];
        $fileSize  = (int) $file['size'];

        // Tạo thư mục uploads nếu chưa có
        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tạo tên file an toàn
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $safeName = uniqid('dataset_', true) . ($ext ? '.' . $ext : '');

        $destPath = $uploadDir . '/' . $safeName;

        if (!move_uploaded_file($tmpName, $destPath)) {
            http_response_code(500);
            echo json_encode(['message' => 'Không lưu được file trên server'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Cập nhật thông tin file vào DB
        $ok = $this->module->updateDatasetFile($id, $safeName, $fileSize);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['message' => 'Dataset không tồn tại hoặc không cập nhật được file'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'message'   => 'Upload file thành công',
            'file_name' => $safeName,
            'file_size' => $fileSize,
        ], JSON_UNESCAPED_UNICODE);
    }
}
