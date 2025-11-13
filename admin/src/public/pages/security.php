<?php
// Đường dẫn đúng đến SecurityHelper.php
require_once __DIR__ . '/../../helpers/SecurityHelper.php';
?>

<div class="security-page">
    <h2>🔐 Bảo mật & Quyền riêng tư</h2>
    <p>Hệ thống <b>Chợ Dữ Liệu</b> áp dụng mã hóa, xác thực và tuân thủ quy định để bảo vệ dữ liệu người dùng.</p>

    <h3>1️⃣ Mã hóa dữ liệu</h3>
    <pre>
<?php
$sample = "Thông tin nhạy cảm - Demo";
$enc = SecurityHelper::encrypt($sample);
$dec = SecurityHelper::decrypt($enc);
echo "🔒 Mã hóa: " . htmlspecialchars($enc) . "\n";
echo "🔓 Giải mã: " . htmlspecialchars($dec);
?>
    </pre>

    <h3>2️⃣ Token truy cập API</h3>
    <pre>
<?php
$token = SecurityHelper::generateApiToken(1, 'admin');
$verify = SecurityHelper::verifyApiToken($token);
echo "🔑 Token: " . htmlspecialchars($token) . "\n";
echo "✅ Xác thực: " . ($verify ? 'Hợp lệ' : 'Không hợp lệ');
?>
    </pre>

    <h3>3️⃣ Tuân thủ & quyền riêng tư</h3>
    <ul>
        <li>Tuân thủ Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân.</li>
        <li>Dữ liệu người dùng được mã hóa và chỉ truy cập qua API bảo mật.</li>
        <li>Thực hiện sao lưu định kỳ & kiểm tra truy cập hệ thống.</li>
    </ul>
</div>
