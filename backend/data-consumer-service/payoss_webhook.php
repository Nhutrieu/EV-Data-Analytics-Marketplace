<?php
require_once __DIR__ . '/classes/Payment.php';
require_once __DIR__ . '/classes/Database.php';
     
$inputRaw = file_get_contents('php://input');
var_dump($inputRaw);  // xem dữ liệu raw nhận được
$input = json_decode($inputRaw, true);
var_dump($input);

// ==========================
// Cấu hình
// ==========================
$CHECKSUM_KEY = "4fd99a4e99c0de621e3a11d69d54b447fc76bf8f61fef9ac0080432f1ef46c2e"; // Thay bằng checksum key thật
$LOG_FILE = __DIR__ . '/payos_webhook.log';

// ==========================
// Nhận payload
// ==========================
$inputRaw = file_get_contents('php://input');
$headers = getallheaders();
$signature = $headers['X-PayOS-Checksum'] ?? '';

$input = json_decode($inputRaw, true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// ==========================
// Validate checksum
// ==========================
function validateChecksum($data, $signature, $checksumKey) {
    ksort($data);
    $strArr = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            ksort($value);
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $value = ($value === null || $value === "undefined" || $value === "null") ? "" : $value;
        $strArr[] = $key . "=" . $value;
    }
    $dataStr = implode("&", $strArr);
    $calculated = hash_hmac("sha256", $dataStr, $checksumKey);
    return hash_equals($calculated, $signature);
}



// ==========================
// Ghi log
// ==========================
file_put_contents($LOG_FILE, date('Y-m-d H:i:s') . " | Payload: " . $inputRaw . " | Valid: " . ($isValid?'yes':'no') . "\n", FILE_APPEND);



// ==========================
// Xử lý thanh toán
// ==========================
$db = Database::getConnection();
$payment = new Payment($db);

$orderId = $input['data']['orderCode'] ?? null;
$status  = $input['data']['status'] ?? null;

if ($orderId && $status === 'success') {
    $payment->markAsPaid($orderId);
}

// ==========================
// Trả về payOS
// ==========================
http_response_code(200);
echo json_encode(['success' => true]);
