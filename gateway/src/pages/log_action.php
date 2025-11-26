<?php
function log_action($conn, $user_email, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO system_logs (user_email, action, description, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $user_email, $action, $description);
    $stmt->execute();
}
?>
