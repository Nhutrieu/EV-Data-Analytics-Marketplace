<?php
session_start();
require_once 'check_maintenance.php';
require_once 'db_connect.php';

// Kiểm tra admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Xử lý trả lời admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_submit'])) {
    $contact_id = $_POST['contact_id'];
    $response = trim($_POST['response']);
    if ($contact_id && $response) {
        $sql = "UPDATE contacts SET response=?, status='replied', updated_at=NOW() WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $response, $contact_id);
        mysqli_stmt_execute($stmt);
    }
}

// Lấy danh sách liên hệ
$sql = "SELECT * FROM contacts ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$contacts = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý liên hệ - Admin</title>
<style>
table {width:100%;border-collapse:collapse;}
table, th, td {border:1px solid #ccc;}
th, td {padding:8px;text-align:left;}
textarea {width:100%;height:60px;}
button {padding:4px 8px;}
</style>
</head>
<body>
<h1>Quản lý liên hệ</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Nội dung</th>
            <th>Trả lời</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contacts as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['message']) ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="contact_id" value="<?= $c['id'] ?>">
                    <textarea name="response"><?= htmlspecialchars($c['response'] ?? '') ?></textarea>
                    <button type="submit" name="reply_submit">Gửi trả lời</button>
                </form>
            </td>
            <td><?= $c['status'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
