<?php
// controllers/PaymentController.php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/RevenueShare.php';
require_once __DIR__ . '/../helpers.php';

class PaymentController {
    private $pdo;
    private $txModel;
    private $cfg;

    function __construct($pdo) {
        $this->pdo = $pdo;
        $this->txModel = new Transaction($pdo);
        $cfgPath = __DIR__ . '/../config.php';
        $this->cfg = file_exists($cfgPath) ? require $cfgPath : ['provider_share_pct' => 70];
    }

    function listTransactions() {
        return $this->txModel->listAll();
    }

    function createTransaction($dataset_id, $consumer_id) {
        $stmt = $this->pdo->prepare("SELECT price, provider_id FROM datasets WHERE id=:id LIMIT 1");
        $stmt->execute([':id' => $dataset_id]);
        $d = $stmt->fetch();
        if (!$d) return ['error' => 'Dataset not found'];
        $amount = (float)$d['price'];
        $provider_share = round($amount * ($this->cfg['provider_share_pct'] / 100), 2);
        $txid = $this->txModel->create($dataset_id, $consumer_id, $amount, $provider_share);
        return ['txid' => $txid, 'payment_url' => '/pay/mock?tx=' . $txid];
    }

    function completeTransaction($txid) {
        $this->txModel->complete($txid);
        $stmt = $this->pdo->prepare("SELECT dataset_id, provider_share FROM transactions WHERE id=:id");
        $stmt->execute([':id' => $txid]);
        $row = $stmt->fetch();
        if ($row) {
            $s = $this->pdo->prepare("SELECT provider_id FROM datasets WHERE id=:id");
            $s->execute([':id' => $row['dataset_id']]);
            $prov = $s->fetchColumn();
            $p = $this->pdo->prepare("
                INSERT INTO revenue_share (provider_id, transaction_id, share_amount)
                VALUES (?, ?, ?)
            ");
            $p->execute([$prov, $txid, $row['provider_share']]);

            $u = $this->pdo->prepare("
                INSERT INTO admin_stats (dataset_id, purchases, last_purchased)
                VALUES (?,1,NOW())
                ON DUPLICATE KEY UPDATE purchases = purchases + 1, last_purchased = NOW()
            ");
            $u->execute([$row['dataset_id']]);
        }
        return ['ok' => true];
    }
}

/* ========================================================
   PHẦN HIỂN THỊ DỮ LIỆU GIAO DỊCH & DOANH THU
======================================================== */

function showTransactions() {
    global $pdo;
    echo "<h2>💰 Danh sách Giao dịch</h2>";

    $stmt = $pdo->query("
    SELECT id, dataset_id, consumer_id, amount, provider_share, status, created_at
    FROM transactions
    ORDER BY created_at DESC
");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "<p>Không có giao dịch nào.</p>";
        return;
    }

    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr style='background:#007bff;color:white;'>
            <th>ID</th>
            <th>Dataset ID</th>
            <th>Consumer ID</th>
            <th>Số tiền</th>
            <th>Chia sẻ cho Provider</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
          </tr>";

    foreach ($rows as $r) {
        echo "<tr>";
        echo "<td>{$r['id']}</td>";
        echo "<td>{$r['dataset_id']}</td>";
        echo "<td>{$r['consumer_id']}</td>";
        echo "<td>" . number_format($r['amount'], 2) . " ₫</td>";
        echo "<td>" . number_format($r['provider_share'], 2) . " ₫</td>";
        echo "<td>{$r['status']}</td>";
        echo "<td>{$r['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

function showRevenueShare() {
    global $pdo;
    echo "<h2>💸 Chia sẻ doanh thu cho Providers</h2>";

    // Đọc tỉ lệ chia từ config.php (mặc định 70%)
    $cfgPath = __DIR__ . '/../config.php';
    $cfg = file_exists($cfgPath) ? require $cfgPath : ['provider_share_pct' => 70];
    $share_pct = $cfg['provider_share_pct'];

    // ✅ Lấy dữ liệu từ bảng transactions + datasets + users, và tính toán chia sẻ trực tiếp
    $stmt = $pdo->query("
        SELECT 
            t.id AS id,
            u.name AS provider,
            ROUND(t.amount * $share_pct / 100, 2) AS share_amount,
            t.created_at
        FROM transactions t
        JOIN datasets d ON d.id = t.dataset_id
        JOIN users u ON u.id = d.provider_id
        ORDER BY t.created_at DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "<p>Chưa có giao dịch nào để chia doanh thu.</p>";
        return;
    }

    // ✅ Hiển thị bảng kết quả
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr style='background:#28a745;color:white;'>
            <th>ID</th>
            <th>Provider</th>
            <th>Số tiền chia sẻ</th>
            <th>Ngày tạo</th>
          </tr>";

    foreach ($rows as $r) {
        echo "<tr>
                <td>{$r['id']}</td>
                <td>{$r['provider']}</td>
                <td>" . number_format($r['share_amount'], 2) . " ₫</td>
                <td>{$r['created_at']}</td>
              </tr>";
    }
    echo "</table>";
}
