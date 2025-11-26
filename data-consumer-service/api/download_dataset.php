<?php
// api/download_dataset.php (consumer)

session_start();

// 1. Kiểm tra login
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Bạn cần đăng nhập để tải dataset.";
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$datasetId = isset($_GET['dataset_id']) ? (int) $_GET['dataset_id'] : 0;

if ($datasetId <= 0) {
    http_response_code(400);
    echo "Thiếu hoặc sai dataset_id.";
    exit;
}

// 2. Kết nối DB
// ⚠️ tuỳ vị trí thực tế của Database.php trong project bạn chỉnh lại đường dẫn require này
require_once __DIR__ . '/../classes/Database.php';

$consumerDb = Database::getConsumerConnection();   // ev_analytics
$providerDb = Database::getProviderConnection();   // ev_data_marketplace

// 3. Kiểm tra purchases: user này đã mua/thuê dataset chưa?
$stmt = $consumerDb->prepare("
    SELECT id, status, type, expiry_date, purchased_at
    FROM purchases
    WHERE user_id = :uid
      AND dataset_id = :dsid
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([
    ':uid'  => $userId,
    ':dsid' => $datasetId,
]);

$purchase = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$purchase) {
    http_response_code(403);
    echo "Bạn chưa mua/thuê dataset này.";
    exit;
}

// 4. Chỉ cho phép nếu status = 'paid'
if ($purchase['status'] !== 'paid') {
    http_response_code(403);
    echo "Thanh toán chưa hoàn tất, không thể tải dataset.";
    exit;
}

// 5. Kiểm tra hết hạn nếu là gói thuê
$canUse = false;
$now    = new DateTime('now');

$type = strtolower(trim($purchase['type'] ?? ''));

// Mua vĩnh viễn
if ($type === 'mua' || $type === 'buy') {
    $canUse = true;
} else {
    // Thuê: phải còn hạn
    if (!empty($purchase['expiry_date'])) {
        try {
            $expiry = new DateTime($purchase['expiry_date']);
            if ($expiry >= $now) {
                $canUse = true;
            }
        } catch (Exception $e) {
            // nếu parse lỗi thì coi như hết hạn
            $canUse = false;
        }
    }
}

if (!$canUse) {
    http_response_code(403);
    echo "Gói thuê đã hết hạn, bạn không thể tải dataset này.";
    exit;
}

// 6. Lấy thông tin file từ DB provider
$stmt = $providerDb->prepare("
    SELECT file_name
    FROM datasets
    WHERE id = :id
      AND status = 'published'
      AND admin_status = 'approved'
    LIMIT 1
");
$stmt->execute([':id' => $datasetId]);
$dataset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dataset || empty($dataset['file_name'])) {
    http_response_code(404);
    echo "Không tìm thấy file cho dataset này.";
    exit;
}

// 7. Tăng lượt tải
$update = $providerDb->prepare("
    UPDATE datasets
    SET downloads = downloads + 1
    WHERE id = :id
");
$update->execute([':id' => $datasetId]);

// 8. Redirect đến file thật ở PROVIDER
// Provider đang map 8008:80 trên máy host
$providerBaseUrl = 'http://localhost:8008'; // nếu sau này đi qua gateway thì đổi URL này

$fileUrl = $providerBaseUrl . '/uploads/' . rawurlencode($dataset['file_name']);

header("Location: $fileUrl");
exit;
