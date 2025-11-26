<?php
// Kết nối DB provider (chứa bảng datasets)
$providerPdo = require __DIR__ . '/../../provider_db.php';

// URL base của PROVIDER để tải file
// Nếu provider của bạn chạy qua gateway thì sửa URL này lại cho đúng
$providerBaseUrl = 'http://localhost:8008'; // 👈 chỉnh nếu cần

echo "<h2>📋 Danh sách Dataset đang chờ kiểm duyệt</h2>";

// ====== XỬ LÝ DUYỆT / TỪ CHỐI ======
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];

    $stmt = $providerPdo->prepare("
        UPDATE datasets
        SET admin_status = 'approved',
            status       = 'published'
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    echo "<script>alert('✅ Đã duyệt dataset ID $id');window.location='?page=moderation';</script>";
    exit;
}

if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];

    $stmt = $providerPdo->prepare("
        UPDATE datasets
        SET admin_status = 'rejected'
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    echo "<script>alert('❌ Đã từ chối dataset ID $id');window.location='?page=moderation';</script>";
    exit;
}

// ====== LẤY DANH SÁCH PENDING ======
// ⚠️ nếu bảng dùng cột name thì đổi d.title thành d.name
$stmt = $providerPdo->query("
    SELECT d.id,
           d.name      AS dataset_name,
           d.provider_id,
           d.price,
           d.created_at,
           d.file_name,
           d.file_size
    FROM datasets d
    WHERE d.admin_status = 'pending'
    ORDER BY d.created_at DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "<p>Không có dataset nào đang chờ duyệt.</p>";
    return;
}
?>

<table border="1" cellpadding="8" cellspacing="0">
    <tr style="background:#007bff;color:white;">
        <th>ID</th>
        <th>Tên Dataset</th>
        <th>Provider</th>
        <th>File</th>
        <th>Giá</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
    </tr>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['dataset_name']) ?></td>
            <td>Provider #<?= (int)$r['provider_id'] ?></td>

            <td>
                <?php if (!empty($r['file_name'])): ?>
                    <?php
                        // Tạo URL download file từ provider
                        $fileUrl = $providerBaseUrl . '/uploads/' . rawurlencode($r['file_name']);
                    ?>
                    <div>
                        <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank">
                            📄 <?= htmlspecialchars($r['file_name']) ?>
                        </a>
                    </div>
                    <?php if (!empty($r['file_size'])): ?>
                        <small><?= round($r['file_size'] / 1024, 2) ?> KB</small>
                    <?php endif; ?>
                <?php else: ?>
                    <em>Chưa upload file</em>
                <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($r['price']) ?></td>
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td>
                <a href="?page=moderation&approve=<?= (int)$r['id'] ?>"
                   style="color:lime;font-weight:bold;">✔ Duyệt</a>
                |
                <a href="?page=moderation&reject=<?= (int)$r['id'] ?>"
                   style="color:red;font-weight:bold;">✖ Từ chối</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
