<?php
require_once __DIR__ . '/../../controllers/AnalyticsController.php';
$ctrl = new AnalyticsController($pdo);

// Lấy tổng quan + dữ liệu trends
$overview = $ctrl->overview();
$trends   = $ctrl->data_trends();

// Gom vào 1 mảng chung để tiện dùng
$stats = [
    "total_users"      => $overview['total_users'],
    "total_providers"  => $overview['total_providers'],
    "total_consumers"  => $overview['total_consumers'],
    "total_datasets"   => $overview['total_datasets'],
    "total_revenue"    => $overview['total_revenue'],

    "revenue_by_day"   => $trends['revenue_over_time'],
    "top_by_purchases" => $trends['top_datasets'],
];

// Nếu người dùng chỉnh sửa doanh thu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['day'], $_POST['amount'])) {
    $ctrl->updateRevenue($_POST['day'], $_POST['amount']);
    echo "<script>alert('Cập nhật doanh thu thành công!'); window.location='?page=analytics';</script>";
    exit;
}
?>

<h2>📈 Phân tích & Báo cáo</h2>

<div style="display:flex;gap:25px;margin-bottom:30px;flex-wrap:wrap;">
    <div style="background:#161b22;padding:15px;border-radius:10px;flex:1;min-width:200px;">
        👥 <b>Người dùng:</b> <?= $stats['total_users'] ?>
    </div>
    <div style="background:#161b22;padding:15px;border-radius:10px;flex:1;min-width:200px;">
        🏢 <b>Nhà cung cấp:</b> <?= $stats['total_providers'] ?>
    </div>
    <div style="background:#161b22;padding:15px;border-radius:10px;flex:1;min-width:200px;">
        🧑‍💻 <b>Người tiêu dùng:</b> <?= $stats['total_consumers'] ?>
    </div>
    <div style="background:#161b22;padding:15px;border-radius:10px;flex:1;min-width:200px;">
        📦 <b>Dataset:</b> <?= $stats['total_datasets'] ?>
    </div>
    <div style="background:#161b22;padding:15px;border-radius:10px;flex:1;min-width:200px;">
        💰 <b>Tổng doanh thu:</b> <?= number_format($stats['total_revenue'], 2) ?> ₫
    </div>
</div>

<h3>💵 Doanh thu theo ngày</h3>
<canvas id="revenueChart" style="max-width:900px; background:#0d1117; padding:20px; border-radius:10px;"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');

const labels = <?= json_encode(array_column($stats['revenue_by_day'], 'day')) ?>;
const dataValues = <?= json_encode(array_column($stats['revenue_by_day'], 'revenue')) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: dataValues,
            borderColor: '#58a6ff',
            backgroundColor: 'rgba(88,166,255,0.3)',
            borderWidth: 2,
            tension: 0.3,
            fill: true,
            pointRadius: 4,
        }]
    }
});
</script>

<h3 style="margin-top:40px;">🔥 Top 10 Dataset được mua nhiều nhất</h3>
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;background:#161b22;color:white;">
    <tr style="background:#238636;color:white;">
        <th>ID</th>
        <th>Tiêu đề Dataset</th>
        <th>Lượt mua</th>
    </tr>
    <?php foreach ($stats['top_by_purchases'] as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= $r['purchases'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>
