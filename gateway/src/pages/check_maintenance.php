<?php


$maintenanceFile = __DIR__ . '/maintenance_mode.txt';
if (!file_exists($maintenanceFile)) {
    file_put_contents($maintenanceFile, "0");
}

$MAINTENANCE_MODE = trim(file_get_contents($maintenanceFile)) === "1";

if ($MAINTENANCE_MODE) {
    $role = $_SESSION['admin']['role'] ?? $_SESSION['user']['role'] ?? null;
    if ($role !== 'admin') {
        header('Location: maintenance_page.php');
        exit;
    }
}
