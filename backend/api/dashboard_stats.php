<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

// Debug: Log request
error_log("Dashboard API called");

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Debug: Check database connection
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    error_log("Database connected successfully");
    
    // Lấy thống kê tổng quan cho user_id = 1
    $user_id = 1;
    
    $stats = [];
    
    // Tổng lượt tải
    $query = "SELECT COUNT(*) as total_downloads FROM downloads d 
              JOIN datasets ds ON d.dataset_id = ds.id 
              WHERE ds.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['downloads'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_downloads'];
    
    // Tổng doanh thu
    $query = "SELECT COALESCE(SUM(amount), 0) as total_revenue FROM downloads d 
              JOIN datasets ds ON d.dataset_id = ds.id 
              WHERE ds.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];
    $stats['revenue'] = number_format($total_revenue, 0, ',', '.') . ' VND';
    
    // Tổng bộ dữ liệu
    $query = "SELECT COUNT(*) as total_datasets FROM datasets WHERE user_id = :user_id AND admin_status = 'approved'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['datasets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_datasets'];
    
    // Tổng người dùng (buyers)
    $query = "SELECT COUNT(DISTINCT buyer_company) as total_users FROM downloads d 
              JOIN datasets ds ON d.dataset_id = ds.id 
              WHERE ds.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Hoạt động gần đây
    $query = "SELECT activity_type, description, amount, created_at 
              FROM activities 
              WHERE user_id = :user_id 
              ORDER BY created_at DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format activities
    $formatted_activities = [];
    foreach ($activities as $activity) {
        $formatted_activities[] = [
            'type' => $activity['activity_type'],
            'description' => $activity['description'],
            'amount' => $activity['amount'] > 0 ? '+' . number_format($activity['amount'], 0, ',', '.') . ' VND' : 'Chờ duyệt',
            'icon' => getActivityIcon($activity['activity_type']),
            'color' => getActivityColor($activity['activity_type']),
            'time' => timeAgo($activity['created_at'])
        ];
    }
    
    // User info
    $query = "SELECT company_name, email FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("API response successful");
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'activities' => $formatted_activities,
        'user' => [
            'company_name' => $user['company_name'],
            'avatar' => substr($user['company_name'], 0, 1)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'debug' => 'Check error logs for details'
    ]);
}

function getActivityIcon($type) {
    $icons = [
        'download' => 'fa-download',
        'upload' => 'fa-upload',
        'user_join' => 'fa-user-plus',
        'purchase' => 'fa-shopping-cart'
    ];
    return $icons[$type] ?? 'fa-bell';
}

function getActivityColor($type) {
    $colors = [
        'download' => '#00d4ff',
        'upload' => '#eab308',
        'user_join' => '#06b6d4',
        'purchase' => '#10b981'
    ];
    return $colors[$type] ?? '#94a3b8';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff/60) . ' phút trước';
    if ($diff < 86400) return floor($diff/3600) . ' giờ trước';
    return floor($diff/86400) . ' ngày trước';
}
?>