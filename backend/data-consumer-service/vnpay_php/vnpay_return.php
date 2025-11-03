<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$msg = ''; // ⚡ Khởi tạo để tránh undefined variable

// ==========================
// Cấu hình kết nối database
// ==========================
$host = 'localhost';
$db   = 'ev_analytics';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// ==========================
// Cấu hình bí mật VNPay
// ==========================
$vnp_HashSecret = "L7HOO3SFZZB7ZSGELCAT2QJZ62B1DJRH";

// ==========================
// Kết nối Database
// ==========================
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// ==========================
// Lấy dữ liệu từ VNPay trả về
// ==========================
$inputData = [];
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

// Lấy SecureHash từ VNPay
$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
unset($inputData['vnp_SecureHash']);
unset($inputData['vnp_SecureHashType']);

// ==========================
// Tạo hash kiểm tra chữ ký
// ==========================
ksort($inputData);
$hashData = http_build_query($inputData, '', '&'); // ❗ Không dùng urldecode
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$isValid = ($secureHash === $vnp_SecureHash);

// Lấy mã phản hồi
$responseCode = $inputData['vnp_ResponseCode'] ?? '';
$order_id     = $inputData['vnp_TxnRef'] ?? '';
$user_id      = $_SESSION['user_id'] ?? null; // ⚡ Thêm nếu user đã login

// Debug log hash
file_put_contents(__DIR__ . '/debug_hash.txt',
    "hashData=" . $hashData . PHP_EOL .
    "secureHash(PHP)=" . $secureHash . PHP_EOL .
    "vnp_SecureHash(VNPay)=" . $vnp_SecureHash . PHP_EOL .
    "isValid=" . ($isValid ? 'TRUE' : 'FALSE') . PHP_EOL .
    str_repeat("-", 80) . PHP_EOL,
    FILE_APPEND
);

// ==========================
// Nếu thanh toán thành công thì lưu vào DB
// ==========================
if ($isValid && $responseCode == '00') {
    try {
        // 1️⃣ Lưu vào payments
        $stmt = $pdo->prepare("
            INSERT INTO payments 
            (order_id, thanh_vien, money, note, vnp_response_code, code_vnpay, code_bank, time)
            VALUES (:order_id, :thanh_vien, :money, :note, :vnp_response_code, :code_vnpay, :code_bank, NOW())
        ");
        $stmt->execute([
            ':order_id'         => $order_id,
            ':thanh_vien'       => $user_id ?: 'Khách sandbox',
            ':money'            => ($inputData['vnp_Amount'] ?? 0) / 100,
            ':note'             => $inputData['vnp_OrderInfo'] ?? '',
            ':vnp_response_code'=> $responseCode,
            ':code_vnpay'       => $inputData['vnp_TransactionNo'] ?? '',
            ':code_bank'        => $inputData['vnp_BankCode'] ?? '',
        ]);

        $msg = "✅ Giao dịch thành công và đã được lưu vào hệ thống!";
// ✅ Xóa giỏ hàng ở localStorage trên trình duyệt
echo "<script>
    localStorage.removeItem('cart');
    localStorage.removeItem('pendingOrder');
</script>";

        // 2️⃣ Lưu vào purchases nếu user đăng nhập
        if ($user_id) {
            // Giả sử $inputData['vnp_OrderInfo'] chứa JSON danh sách gói mua
            // Ví dụ: [{"dataset_id":1,"type":"buy","price":2500000}, {"dataset_id":2,"type":"rent_month","price":500000}]
            $purchases = json_decode($inputData['vnp_OrderInfo'] ?? '[]', true);
            if (is_array($purchases)) {
                $stmtPur = $pdo->prepare("
                    INSERT INTO purchases (user_id, dataset_id, type, price, purchased_at)
                    VALUES (:user_id, :dataset_id, :type, :price, NOW())
                ");
                foreach ($purchases as $p) {
                    $stmtPur->execute([
                        ':user_id'     => $user_id,
                        ':dataset_id'  => $p['dataset_id'] ?? 0,
                        ':type'        => $p['type'] ?? 'buy',
                        ':price'       => $p['price'] ?? 0,
                    ]);
                }
            }
        }

    } catch (Exception $e) {
        $msg = "⚠ Giao dịch thành công nhưng lỗi khi lưu vào DB: " . $e->getMessage();
    }
} elseif (!$isValid) {
    $msg = "❌ Sai chữ ký! Dữ liệu không hợp lệ.";
} else {
    $msg = "❌ Giao dịch thất bại (Mã lỗi: {$responseCode})";
}

// ==========================
// Ghi log giao dịch
// ==========================
file_put_contents(__DIR__ . '/vnpay_log.txt',
    date('c') . " | " . json_encode($_GET, JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Kết quả thanh toán VNPay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding-top: 50px; }
        .card { max-width: 600px; margin: auto; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-header { background-color: #007bff; color: #fff; font-weight: bold; }
        .btn-back { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 8px; }
        .btn-back:hover { background-color: #0056b3; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header text-center">
        KẾT QUẢ THANH TOÁN VNPay
    </div>
    <div class="card-body">
        <p><strong>Mã đơn hàng:</strong> <?= htmlspecialchars($order_id ?: '—') ?></p>
        <p><strong>Số tiền:</strong> <?= number_format(($inputData['vnp_Amount'] ?? 0) / 100) ?> VNĐ</p>
        <p><strong>Nội dung:</strong> <?= htmlspecialchars($inputData['vnp_OrderInfo'] ?? '') ?></p>
        <p><strong>Ngân hàng:</strong> <?= htmlspecialchars($inputData['vnp_BankCode'] ?? '') ?></p>
        <p><strong>Mã GD VNPay:</strong> <?= htmlspecialchars($inputData['vnp_TransactionNo'] ?? '') ?></p>
        <p><strong>Thời gian thanh toán:</strong> <?= htmlspecialchars($inputData['vnp_PayDate'] ?? '') ?></p>
        <hr>
        <h5 class="mt-3 text-center"><?= $msg ?></h5>
        <div class="text-center mt-4">
            <a href="http://localhost/EV-Data-Analytics-Marketplace/public/consumer.html">
                <button class="btn-back">⬅ Quay lại trang chính</button>
            </a>
        </div>
    </div>
</div>
</body>
</html>
