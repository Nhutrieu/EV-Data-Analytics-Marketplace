<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../controllers/AnalyticsController.php';

$an = new AnalyticsController($pdo);
$trends = $an->data_trends();
?>

<h2>🤖 Phân tích & Dự báo xu hướng dữ liệu</h2>

<!-- ================= TOP DATASETS ================ -->
<div class="report-box">
    <h3>📌 Top 10 datasets được mua nhiều nhất</h3>
    <table class="user-table">
        <tr><th>ID</th><th>Dataset</th><th>Lượt mua</th></tr>
        <?php foreach ($trends['top_datasets'] as $d): ?>
        <tr>
            <td><?= $d['id'] ?></td>
            <td><?= $d['title'] ?></td>
            <td><?= $d['purchases'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- ================= DOANH THU THEO THỜI GIAN ================ -->
<div class="report-box">
    <h3>📈 Doanh thu theo thời gian</h3>
    <ul>
        <?php foreach ($trends['revenue_over_time'] as $r): ?>
            <li><?= $r['day'] ?> → <?= number_format($r['revenue']) ?>₫</li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- ================= AI FORECAST ================ -->
<div class="report-box">
    <h3>🤖 AI dự báo xu hướng</h3>

    <p><b>Xu hướng:</b> <?= $trends['forecast']['trend'] ?></p>

    <p><b>Ghi chú:</b> 
        <?= $trends['forecast']['note'] ?? "Không có dữ liệu." ?>
    </p>
</div>
