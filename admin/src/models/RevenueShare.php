<?php
// models/RevenueShare.php

class RevenueShare {
    private $pdo;

    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    function listAll() {
        $stmt = $this->pdo->query("
            SELECT r.id, u.name AS provider_name, r.transaction_id, r.share_amount, r.created_at
            FROM revenue_share r
            JOIN users u ON u.id = r.provider_id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
