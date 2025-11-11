<?php
require_once __DIR__ . '/../../controllers/AnalyticsController.php';
$ctrl = new AnalyticsController($pdo);
$stats = $ctrl->overview();

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
const data = {
    labels: <?= json_encode(array_column($stats['revenue_by_day'], 'day')) ?>,
    datasets: [{
        label: 'Doanh thu (VNĐ)',
        data: <?= json_encode(array_column($stats['revenue_by_day'], 'total')) ?>,
        borderColor: '#58a6ff',
        backgroundColor: 'rgba(88,166,255,0.3)',
        borderWidth: 2,
        tension: 0.3,
        fill: true,
        pointRadius: 4,
    }]
};
new Chart(ctx, { type: 'line', data });
</script>

<h3 style="margin-top:40px;">📝 Chỉnh sửa doanh thu</h3>
<form method="POST" style="background:#161b22;padding:15px;border-radius:10px;width:400px;">
    <label>Ngày:</label><br>
    <select name="day" style="width:100%;padding:5px;margin:5px 0;">
        <?php foreach ($stats['revenue_by_day'] as $r): ?>
            <option value="<?= $r['day'] ?>"><?= $r['day'] ?></option>
        <?php endforeach; ?>
    </select>
    <label>Doanh thu mới (VNĐ):</label>
    <input type="number" step="0.01" name="amount" required style="width:100%;padding:5px;margin:5px 0;">
    <button type="submit" style="padding:8px 15px;background:#238636;color:white;border:none;border-radius:5px;cursor:pointer;">💾 Lưu thay đổi</button>
</form>

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
