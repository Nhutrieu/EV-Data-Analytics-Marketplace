<?php
require_once __DIR__ . '/../../db.php';

echo "<h2>📋 Danh sách Dataset đang chờ kiểm duyệt</h2>";

$stmt = $pdo->query("
    SELECT d.id, d.title, u.name AS provider, d.price, d.created_at
    FROM datasets d
    JOIN users u ON u.id = d.provider_id
    WHERE d.status = 'pending'
    ORDER BY d.created_at DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "<p>✅ Không có dữ liệu nào đang chờ duyệt.</p>";
    return;
}

echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr style='background:#007bff;color:white;'>
        <th>ID</th>
        <th>Tên Dataset</th>
        <th>Provider</th>
        <th>Giá</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
      </tr>";

foreach ($rows as $r) {
    echo "<tr>
            <td>{$r['id']}</td>
            <td>{$r['title']}</td>
            <td>{$r['provider']}</td>
            <td>" . number_format($r['price'], 2) . " ₫</td>
            <td>{$r['created_at']}</td>
            <td>
                <a href='?page=moderation&approve={$r['id']}' style='color:green;'>✅ Duyệt</a> |
                <a href='?page=moderation&reject={$r['id']}' style='color:red;'>❌ Từ chối</a>
            </td>
          </tr>";
}
echo "</table>";

// Xử lý duyệt / từ chối
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE datasets SET status='approved' WHERE id=?")->execute([$id]);
    echo "<script>alert('✅ Đã duyệt dataset ID $id');window.location='?page=moderation';</script>";
}
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $pdo->prepare("UPDATE datasets SET status='rejected' WHERE id=?")->execute([$id]);
    echo "<script>alert('❌ Đã từ chối dataset ID $id');window.location='?page=moderation';</script>";
}
?>
