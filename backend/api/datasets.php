<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$db = $database->getConnection();

// User ID mặc định (trong thực tế sẽ lấy từ session/token)
$user_id = 1;

switch($method) {
    case 'GET':
        getDatasets($db, $user_id);
        break;
    case 'POST':
        addDataset($db, $user_id);
        break;
    case 'PUT':
        updateDataset($db, $user_id);
        break;
    case 'DELETE':
        deleteDataset($db, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function getDatasets($db, $user_id) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    $query = "SELECT * FROM datasets WHERE user_id = :user_id";
    $params = [':user_id' => $user_id];
    
    if (!empty($search)) {
        $query .= " AND (name LIKE :search OR description LIKE :search OR tags LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $datasets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response
    $formatted_datasets = [];
    foreach ($datasets as $dataset) {
        $formatted_datasets[] = [
            'id' => $dataset['id'],
            'name' => $dataset['name'],
            'type' => $dataset['data_type'],
            'format' => $dataset['data_format'],
            'price' => $dataset['price'],
            'priceUnit' => $dataset['price_unit'],
            'status' => $dataset['status'],
            'adminStatus' => $dataset['admin_status'],
            'downloads' => $dataset['download_count'],
            'description' => $dataset['description'],
            'tags' => $dataset['tags'],
            'adminNote' => $dataset['admin_note'],
            'createdAt' => $dataset['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_datasets
    ]);
}

function addDataset($db, $user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $required = ['name', 'data_type', 'data_format', 'price', 'price_unit'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Thiếu thông tin: $field"]);
            return;
        }
    }
    
    try {
        $query = "INSERT INTO datasets (user_id, name, description, data_type, data_format, price, price_unit, tags, status, admin_status) 
                  VALUES (:user_id, :name, :description, :data_type, :data_format, :price, :price_unit, :tags, 'pending', 'pending')";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? '',
            ':data_type' => $data['data_type'],
            ':data_format' => $data['data_format'],
            ':price' => $data['price'],
            ':price_unit' => $data['price_unit'],
            ':tags' => $data['tags'] ?? ''
        ]);
        
        $dataset_id = $db->lastInsertId();
        
        // Thêm activity
        $activity_query = "INSERT INTO activities (user_id, activity_type, description) 
                          VALUES (:user_id, 'upload', :description)";
        $activity_stmt = $db->prepare($activity_query);
        $activity_stmt->execute([
            ':user_id' => $user_id,
            ':description' => 'Tải lên dữ liệu mới - ' . $data['name']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm dữ liệu thành công. Đang chờ admin duyệt.',
            'data_id' => $dataset_id
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

function updateDataset($db, $user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID dữ liệu']);
        return;
    }
    
    try {
        // Kiểm tra quyền sở hữu
        $check_query = "SELECT id FROM datasets WHERE id = :id AND user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([':id' => $data['id'], ':user_id' => $user_id]);
        
        if ($check_stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền chỉnh sửa']);
            return;
        }
        
        $query = "UPDATE datasets SET 
                  name = :name, 
                  description = :description, 
                  data_type = :data_type, 
                  data_format = :data_format, 
                  price = :price, 
                  price_unit = :price_unit, 
                  tags = :tags";
        
        // Nếu đang bị rejected, chuyển về pending khi sửa
        if (isset($data['adminStatus']) && $data['adminStatus'] === 'rejected') {
            $query .= ", status = 'pending', admin_status = 'pending', admin_note = ''";
        }
        
        $query .= " WHERE id = :id AND user_id = :user_id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? '',
            ':data_type' => $data['data_type'],
            ':data_format' => $data['data_format'],
            ':price' => $data['price'],
            ':price_unit' => $data['price_unit'],
            ':tags' => $data['tags'] ?? '',
            ':id' => $data['id'],
            ':user_id' => $user_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã cập nhật dữ liệu thành công'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

function deleteDataset($db, $user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu ID dữ liệu']);
        return;
    }
    
    try {
        // Kiểm tra quyền sở hữu
        $check_query = "SELECT id FROM datasets WHERE id = :id AND user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([':id' => $data['id'], ':user_id' => $user_id]);
        
        if ($check_stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không có quyền xóa']);
            return;
        }
        
        $query = "DELETE FROM datasets WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $data['id'], ':user_id' => $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa dữ liệu thành công'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}
?>