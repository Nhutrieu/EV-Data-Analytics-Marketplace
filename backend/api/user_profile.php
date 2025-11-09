<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

$user_id = 1;

switch($method) {
    case 'GET':
        getUserProfile($db, $user_id);
        break;
    case 'PUT':
        updateUserProfile($db, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function getUserProfile($db, $user_id) {
    $query = "SELECT company_name, email, contact_person, phone, address, description, avatar_color 
              FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'company_name' => $user['company_name'],
                'email' => $user['email'],
                'contact_person' => $user['contact_person'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'description' => $user['description'],
                'avatar' => substr($user['company_name'], 0, 1),
                'avatar_color' => $user['avatar_color']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}

function updateUserProfile($db, $user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $required = ['company_name', 'email', 'contact_person'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Thiếu thông tin: $field"]);
            return;
        }
    }
    
    try {
        $query = "UPDATE users SET 
                  company_name = :company_name,
                  email = :email,
                  contact_person = :contact_person,
                  phone = :phone,
                  address = :address,
                  description = :description,
                  updated_at = NOW()
                  WHERE id = :user_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':company_name' => $data['company_name'],
            ':email' => $data['email'],
            ':contact_person' => $data['contact_person'],
            ':phone' => $data['phone'] ?? '',
            ':address' => $data['address'] ?? '',
            ':description' => $data['description'] ?? '',
            ':user_id' => $user_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã cập nhật thông tin thành công'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}
?>