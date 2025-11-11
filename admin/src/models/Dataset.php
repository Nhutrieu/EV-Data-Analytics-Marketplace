<?php
class Dataset {
  private $pdo;
  function __construct($pdo){ $this->pdo = $pdo; }
  function create($provider_id,$title,$desc,$price,$file_path,$meta_json='{}') {
    $stmt = $this->pdo->prepare("INSERT INTO datasets (provider_id,title,description,price,file_path,metadata,status) VALUES (?,?,?,?,?,?, 'pending')");
    $stmt->execute([$provider_id,$title,$desc,$price,$file_path,$meta_json]);
    return $this->pdo->lastInsertId();
  }
  function findPending() {
    $stmt = $this->pdo->query("SELECT d.*, u.name as provider_name FROM datasets d LEFT JOIN users u ON d.provider_id=u.id WHERE d.status='pending'");
    return $stmt->fetchAll();
  }
  function setStatus($id,$status) {
    $stmt = $this->pdo->prepare("UPDATE datasets SET status=:s WHERE id=:id");
    $stmt->execute([':s'=>$status,':id'=>$id]);
  }
  function topByViews($limit=10) {
    // If admin_stats dataset stats exist, join
    $stmt = $this->pdo->prepare("SELECT d.id,d.title,IFNULL(a.views,0) as views,IFNULL(a.purchases,0) as purchases FROM datasets d LEFT JOIN admin_stats a ON d.id=a.dataset_id ORDER BY a.views DESC LIMIT :l");
    $stmt->bindValue(':l',(int)$limit,PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
}
