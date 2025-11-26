<?php
session_start();
require_once 'check_maintenance.php';
if(!isset($_SESSION['user'])){
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>API Docs - EV Data Marketplace</title>
<link rel="stylesheet" href="../public/assets/css/home.css">
<style>
.container { max-width:1100px; margin:40px auto; padding:30px; background:#1e1e2f; border-radius:16px; color:#fff; }
h1,h2,h3 { color:#e2e8f0; }
.section { margin-top:40px; }
.api-endpoint { background: rgba(255,255,255,0.05); padding:20px; border-radius:12px; margin-bottom:20px; }
.api-endpoint h4 { color:#06b6d4; margin-bottom:10px; }
.api-endpoint p { margin:6px 0; line-height:1.5; }
.code-block { background:#111; color:#0ff; padding:10px; border-radius:8px; font-family: monospace; overflow-x:auto; margin-top:6px; }
.btn { display:inline-block; padding:8px 16px; border-radius:8px; text-decoration:none; color:#04141a; font-weight:700; margin-top:10px; transition:0.2s; }
.btn.primary { background: linear-gradient(90deg,#06b6d4,#10b981); }
.btn:hover { transform: translateY(-2px); }
</style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <header>
        <div class="logo">
            <div class="mark">EV</div>
            <div>
                <div class="title">EV Data Marketplace</div>
                <div class="subtitle">Chợ dữ liệu & công cụ phân tích xe điện</div>
            </div>
        </div>
        <nav>
            <a href="home_logged.php">Trang chủ</a>
            <a href="datasets.php">Bộ sưu tập dữ liệu</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="contact.php">Liên hệ</a>
            <a href="logout.php" class="cta logout">Đăng xuất (<?= htmlspecialchars($user['username']) ?>)</a>
        </nav>
    </header>

    <main>
        <h1>📚 API Documentation</h1>
        <p>Hướng dẫn sử dụng API của EV Data Marketplace. Tất cả endpoints đều yêu cầu <code>api_key</code> của bạn.</p>

        <!-- Endpoint 1 -->
        <div class="api-endpoint">
            <h4>GET /api/datasets</h4>
            <p>Lấy danh sách tất cả dataset.</p>
            <p><strong>Query parameters:</strong></p>
            <div class="code-block">
api_key=YOUR_API_KEY<br>
limit=10 (tùy chọn)<br>
offset=0 (tùy chọn)
            </div>
            <p><strong>Response (JSON):</strong></p>
            <div class="code-block">
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "EV Sales by Country",
            "source": "EV Data Corp",
            "size": "5MB",
            "description": "Doanh số EV theo quốc gia 2020-2025"
        },
        ...
    ]
}
            </div>
            <a href="api_test.php?endpoint=datasets" class="btn primary">Thử API</a>
        </div>

        <!-- Endpoint 2 -->
        <div class="api-endpoint">
            <h4>GET /api/dataset?id=ID</h4>
            <p>Lấy chi tiết một dataset theo <code>id</code>.</p>
            <p><strong>Query parameters:</strong></p>
            <div class="code-block">
api_key=YOUR_API_KEY<br>
id=1
            </div>
            <p><strong>Response (JSON):</strong></p>
            <div class="code-block">
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "EV Sales by Country",
        "source": "EV Data Corp",
        "size": "5MB",
        "description": "Doanh số EV theo quốc gia 2020-2025",
        "download_url": "/downloads/ev_sales.csv"
    }
}
            </div>
            <a href="api_test.php?endpoint=dataset&id=1" class="btn primary">Thử API</a>
        </div>

        <!-- Endpoint 3 -->
        <div class="api-endpoint">
            <h4>GET /api/reports</h4>
            <p>Lấy báo cáo phân tích dữ liệu.</p>
            <p><strong>Query parameters:</strong></p>
            <div class="code-block">
api_key=YOUR_API_KEY<br>
type=sales (tùy chọn)
            </div>
            <p><strong>Response (JSON):</strong></p>
            <div class="code-block">
{
    "status": "success",
    "reports": [
        {
            "id": 1,
            "title": "EV Sales 2020-2025",
            "file": "/reports/ev_sales.pdf"
        },
        ...
    ]
}
            </div>
            <a href="api_test.php?endpoint=reports" class="btn primary">Thử API</a>
        </div>
    </main>

<?php include 'footer.php'; ?>
</div>
</body>
</html>
