<?php
require_once __DIR__ . '/../classes/Analytics.php';

class AnalyticsModule {

    private $analytics;

    public function __construct() {
        $this->analytics = new Analytics();
    }

    public function getAllPackages() {
        return $this->analytics->getAllPackages();
    }

    public function getPackageById($id) {
        return $this->analytics->getPackageById($id);
    }

    public function getPackageData($id) {
        return $this->analytics->getPackageData($id);
    }
}
