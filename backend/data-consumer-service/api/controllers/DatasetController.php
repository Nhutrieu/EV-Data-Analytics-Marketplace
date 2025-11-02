<?php
require_once __DIR__ . '/../../classes/Dataset.php';

class DatasetController {
    private $dataset;

    public function __construct() {
        $this->dataset = new Dataset();
    }

    public function listDatasets() {
        $data = $this->dataset->getAll();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    }

    public function viewDataset($id) {
        $data = $this->dataset->getById($id);
        if ($data) {
            echo json_encode(["success" => true, "data" => $data]);
        } else {
            echo json_encode(["success" => false, "message" => "Dataset not found"]);
        }
    }
}
?>
