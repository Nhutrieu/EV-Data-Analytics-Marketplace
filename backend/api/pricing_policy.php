<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

// User ID mặc định
$user_id = 1;

switch($method) {
    case 'GET':
        getPricingPolicy($db, $user_id);
        break;
    case 'POST':
    case 'PUT':
        savePricingPolicy($db, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function getPricingPolicy($db, $user_id) {
    $query = "SELECT * FROM pricing_policies WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $policy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$policy) {
        // Tạo policy mặc định nếu chưa có
        $policy = [
            'default_model' => 'per-download',
            'default_price' => 500000.00,
            'default_currency' => 'VND',
            'default_usage_rights' => 'commercial',
            'default_license' => 'Dữ liệu được cung cấp bởi EV Data Analytics Marketplace...'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'policy' => $policy
    ]);
}

function savePricingPolicy($db, $user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $required = ['default_model', 'default_price', 'default_currency', 'default_usage_rights'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Thiếu thông tin: $field"]);
            return;
        }
    }
    
    try {
        // Kiểm tra đã có policy chưa
        $check_query = "SELECT id FROM pricing_policies WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([':user_id' => $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            // Update
            $query = "UPDATE pricing_policies SET 
                      default_model = :default_model,
                      default_price = :default_price,
                      default_currency = :default_currency,
                      default_usage_rights = :default_usage_rights,
                      default_license = :default_license,
                      updated_at = NOW()
                      WHERE user_id = :user_id";
        } else {
            // Insert
            $query = "INSERT INTO pricing_policies 
                      (user_id, default_model, default_price, default_currency, default_usage_rights, default_license) 
                      VALUES 
                      (:user_id, :default_model, :default_price, :default_currency, :default_usage_rights, :default_license)";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':default_model' => $data['default_model'],
            ':default_price' => $data['default_price'],
            ':default_currency' => $data['default_currency'],
            ':default_usage_rights' => $data['default_usage_rights'],
            ':default_license' => $data['default_license'] ?? ''
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã lưu chính sách giá thành công'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}
?>