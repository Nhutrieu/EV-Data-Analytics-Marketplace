<?php
require_once '../db.php';
require_once '../controllers/UserController.php';
require_once '../controllers/PaymentController.php';
require_once '../controllers/AnalyticsController.php';

$userCtrl = new UserController($pdo);
$payCtrl  = new PaymentController($pdo);
$anCtrl   = new AnalyticsController($pdo);

// 🟢 Xử lý thêm người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addUser'])) {
    $userCtrl->createUser([
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'role' => $_POST['role']
    ]);
    header("Location: ?page=users");
    exit;
}

// 🟢 Xử lý xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteUser'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ?page=users");
    exit;
}

$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header style="
    display:flex; 
    justify-content:space-between; 
    align-items:center; 
    padding:15px 20px; 
    background:#1e1e2f; 
    color:white; 
    font-size:20px; 
    font-weight:600;
    box-shadow:0 2px 8px rgba(0,0,0,0.2);
">
    <div>🏛️ Bảng điều khiển Admin - Chợ Dữ Liệu</div>

    <a href="http://localhost:8006/src/pages/home.php" 
       style="
           padding:10px 18px;
           background:linear-gradient(135deg,#ff4b4b,#c62828);
           color:white;
           border-radius:8px;
           text-decoration:none;
           font-size:14px;
           font-weight:600;
           box-shadow:0 4px 10px rgba(0,0,0,0.25);
           transition:0.25s ease;
       "
       onmouseover="this.style.transform='scale(1.07)'"
       onmouseout="this.style.transform='scale(1)'"
    >
        🔓 Đăng xuất
    </a>
</header>

<div class="sidebar">
    <h3>⚙️ Chức năng chính</h3>

    <!-- 🧩 Quản lý người dùng -->
    <div class="menu-item <?= in_array($page,['users','moderation']) ? 'active' : '' ?>" onclick="toggleMenu('user')">👥 Quản lý người dùng</div>
    <div class="submenu" id="submenu-user" style="<?= in_array($page,['users','moderation']) ? 'display:block' : 'display:none' ?>">
        <a href="?page=users" class="<?= $page==='users' ? 'active' : '' ?>">Danh sách người dùng</a>
        <a href="?page=moderation" class="<?= $page==='moderation' ? 'active' : '' ?>">Kiểm duyệt dữ liệu</a>
    </div>

    <!-- 💰 Thanh toán & Doanh thu -->
    <div class="menu-item <?= in_array($page,['transactions','revenues']) ? 'active' : '' ?>" onclick="toggleMenu('payment')">💰 Thanh toán & Doanh thu</div>
    <div class="submenu" id="submenu-payment" style="<?= in_array($page,['transactions','revenues']) ? 'display:block' : 'display:none' ?>">
        <a href="?page=transactions" class="<?= $page==='transactions' ? 'active' : '' ?>">Giao dịch</a>
        <a href="?page=revenues" class="<?= $page==='revenues' ? 'active' : '' ?>">Chia sẻ doanh thu</a>
    </div>

    <!-- 📊 Phân tích & Báo cáo -->
<div class="menu-item <?= in_array($page,['analytics','analytics_ai']) ? 'active' : '' ?>" onclick="toggleMenu('analytics')">
    📊 Phân tích dữ liệu
</div>

<div class="submenu" id="submenu-analytics" style="<?= in_array($page,['analytics','analytics_ai']) ? 'display:block':'display:none' ?>">
    <a href="?page=analytics" class="<?= $page==='analytics' ? 'active' : '' ?>">Báo cáo tổng hợp</a>
    <a href="?page=analytics_ai" class="<?= $page==='analytics_ai' ? 'active' : '' ?>">AI phân tích & dự báo</a>
</div>

    <div class="menu-item <?= $page==='security' ? 'active' : '' ?>" onclick="window.location='?page=security'">🔐 Bảo mật & Quyền riêng tư</div>
</div>

<div class="content">
<?php
switch ($page) {
    case 'transactions':
        showTransactions();
        break;

    case 'revenues':
        showRevenueShare();
        break;

    case 'users':
        echo "<h2>👥 Danh sách người dùng</h2>";

        // === PROVIDERS ===
        echo "<h3>🏪 Provider (Người cung cấp dữ liệu)</h3>";
        $providers = $userCtrl->getProviders();
        echo "<table class='user-table'>
                <tr><th>ID</th><th>Tên</th><th>Email</th><th>Vai trò</th><th>Hành động</th></tr>";
        foreach ($providers as $u) {
            echo "<tr>
                    <td>{$u['id']}</td>
                    <td>{$u['name']}</td>
                    <td>{$u['email']}</td>
                    <td>{$u['role']}</td>
                    <td>
                        <form method='POST' style='display:inline'>
                            <input type='hidden' name='delete_id' value='{$u['id']}'>
                            <button type='submit' name='deleteUser' onclick='return confirm(\"Xóa người dùng này?\")'>🗑️</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";

        // === CONSUMERS ===
        echo "<h3>👤 Consumer (Người tiêu dùng dữ liệu)</h3>";
        $consumers = $userCtrl->getConsumers();
        echo "<table class='user-table'>
                <tr><th>ID</th><th>Tên</th><th>Email</th><th>Vai trò</th><th>Hành động</th></tr>";
        foreach ($consumers as $u) {
            echo "<tr>
                    <td>{$u['id']}</td>
                    <td>{$u['name']}</td>
                    <td>{$u['email']}</td>
                    <td>{$u['role']}</td>
                    <td>
                        <form method='POST' style='display:inline'>
                            <input type='hidden' name='delete_id' value='{$u['id']}'>
                            <button type='submit' name='deleteUser' onclick='return confirm(\"Xóa người dùng này?\")'>🗑️</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";

        // === FORM THÊM NGƯỜI DÙNG ===
        echo "<hr>";
        echo "<h3>➕ Thêm người dùng mới</h3>";
        echo "
        <form method='POST' class='user-form'>
            <input type='text' name='name' placeholder='Tên người dùng' required>
            <input type='email' name='email' placeholder='Email' required>
            <input type='password' name='password' placeholder='Mật khẩu' required>
            <select name='role'>
                <option value='provider'>Provider</option>
                <option value='consumer'>Consumer</option>
            </select>
            <button type='submit' name='addUser'>Thêm</button>
        </form>";
        break;

    case 'moderation':
        include __DIR__ . '/pages/moderation.php';
        break;

    case 'analytics':
        include __DIR__ . '/pages/analytics.php';
        break;
    case 'analytics_ai':
    include __DIR__ . '/pages/analytics_ai.php';
    break;


    case 'security':
        include __DIR__ . '/pages/security.php';
        break;

    default:
        echo "<h2>👋 Chào mừng đến hệ thống Quản trị Chợ Dữ Liệu</h2>";
}
?>
</div>

<script>
function toggleMenu(id){
    const sub = document.getElementById('submenu-'+id);
    sub.style.display = (sub.style.display==='block'?'none':'block');
}
</script>
</body>
</html>
