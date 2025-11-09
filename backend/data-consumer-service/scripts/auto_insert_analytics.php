<?php
// backend/data-consumer-service/scripts/auto-insert-analytics.php

require_once __DIR__ . '/../classes/Database.php';

try {
    $db = Database::getConnection();

    // ===============================
    // 🕒 1. THÊM DỮ LIỆU MỚI HÔM NAY
    // ===============================
    $today = date("Y-m-d");

    // Kiểm tra hôm nay đã có dữ liệu chưa
    $check = $db->prepare("
        SELECT COUNT(*) 
        FROM analytics_data 
        WHERE DATE(created_at) = :today
    ");
    $check->execute([':today' => $today]);

    if ($check->fetchColumn() == 0) {
        // Sinh dữ liệu ngẫu nhiên
        $soc = json_encode([rand(70, 90), rand(65, 88), rand(75, 92), rand(60, 85), rand(70, 90)]);
        $soh = json_encode([rand(95, 99), rand(93, 98), rand(96, 99), rand(94, 98), rand(95, 99)]);
        $range = json_encode([rand(100, 160), rand(110, 170), rand(120, 180), rand(90, 150), rand(100, 160)]);
        $consumption = json_encode([rand(12, 20), rand(13, 22), rand(11, 18), rand(14, 19), rand(13, 21)]);
        $vehicle_type = json_encode(["EV" => rand(60, 80), "Hybrid" => rand(20, 40)]);
        $co2 = json_encode([rand(5, 10), rand(6, 9), rand(4, 8), rand(7, 10), rand(5, 9)]);

        $stmt = $db->prepare("
            INSERT INTO analytics_data 
                (analytics_id, dataset_id, created_at, soc, soh, `range`, consumption, vehicle_type, co2_saved)
            VALUES (1, 1, :created_at, :soc, :soh, :range, :consumption, :vehicle_type, :co2)
        ");
        $stmt->execute([
            ':created_at'   => $today,
            ':soc'          => $soc,
            ':soh'          => $soh,
            ':range'        => $range,
            ':consumption'  => $consumption,
            ':vehicle_type' => $vehicle_type,
            ':co2'          => $co2,
        ]);

        echo "✅ Đã thêm dữ liệu mới cho ngày $today\n";
    } else {
        echo "⚠️ Hôm nay ($today) đã có dữ liệu, bỏ qua.\n";
    }

    // =====================================
    // 🧹 2. GIỮ LẠI 8 NGÀY GẦN NHẤT
    // =====================================
    $deleted = $db->exec("
        DELETE FROM analytics_data 
        WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 8 DAY)
    ");
    if ($deleted > 0) {
        echo "🧹 Đã xóa $deleted bản ghi cũ (hơn 8 ngày)\n";
    }

    // =====================================
    // 📊 3. TẠO THỐNG KÊ THÁNG NẾU CHƯA CÓ
    // =====================================
    $lastMonth = date("Y-m", strtotime("first day of last month"));

    $checkSummary = $db->prepare("
        SELECT COUNT(*) 
        FROM analytics_monthly_summary 
        WHERE month_year = :month
    ");
    $checkSummary->execute([':month' => $lastMonth]);

    if ($checkSummary->fetchColumn() == 0) {

        $stmt = $db->prepare("
            SELECT 
                AVG(JSON_EXTRACT(soc, '$[0]'))         AS avg_soc,
                AVG(JSON_EXTRACT(soh, '$[0]'))         AS avg_soh,
                AVG(JSON_EXTRACT(`range`, '$[0]'))     AS avg_range,
                AVG(JSON_EXTRACT(consumption, '$[0]')) AS avg_consumption,
                SUM(JSON_EXTRACT(co2_saved, '$[0]'))   AS co2_saved_total
            FROM analytics_data
            WHERE DATE_FORMAT(created_at, '%Y-%m') = :month
        ");
        $stmt->execute([':month' => $lastMonth]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data && $data['avg_soc'] !== null) {
            $insert = $db->prepare("
                INSERT INTO analytics_monthly_summary 
                    (month_year, avg_soc, avg_soh, avg_range, avg_consumption, co2_saved_total)
                VALUES 
                    (:month, :avg_soc, :avg_soh, :avg_range, :avg_consumption, :co2)
            ");
            $insert->execute([
                ':month'           => $lastMonth,
                ':avg_soc'         => $data['avg_soc'],
                ':avg_soh'         => $data['avg_soh'],
                ':avg_range'       => $data['avg_range'],
                ':avg_consumption' => $data['avg_consumption'],
                ':co2'             => $data['co2_saved_total'],
            ]);
            echo "📅 Đã tạo thống kê tháng $lastMonth ✅\n";
        } else {
            echo "ℹ️ Không có dữ liệu tháng $lastMonth để thống kê.\n";
        }
    } else {
        echo "📊 Tháng $lastMonth đã có thống kê, bỏ qua.\n";
    }

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
