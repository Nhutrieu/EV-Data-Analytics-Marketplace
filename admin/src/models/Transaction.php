<?php
class Transaction {
  private $pdo;
  function __construct($pdo){ $this->pdo = $pdo; }
  function create($dataset_id,$consumer_id,$amount,$provider_share) {
    $stmt = $this->pdo->prepare("INSERT INTO transactions (dataset_id, consumer_id, amount, provider_share, status) VALUES (?,?,?,?, 'pending')");
    $stmt->execute([$dataset_id,$consumer_id,$amount,$provider_share]);
    return $this->pdo->lastInsertId();
  }
  function listAll() {
    $stmt = $this->pdo->query("SELECT t.*, d.title as dataset_title, u.name as consumer_name FROM transactions t LEFT JOIN datasets d ON t.dataset_id=d.id LEFT JOIN users u ON t.consumer_id=u.id ORDER BY t.created_at DESC");
    return $stmt->fetchAll();
  }
  function complete($txid) {
    $stmt = $this->pdo->prepare("UPDATE transactions SET status='completed' WHERE id=:id");
    $stmt->execute([':id'=>$txid]);
  }
}
