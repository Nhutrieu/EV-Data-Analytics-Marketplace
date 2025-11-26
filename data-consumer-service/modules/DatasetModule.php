<?php
// consumer/modules/DatasetModule.php

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Dataset.php';

class DatasetModule
{
    private Dataset $dataset;

    public function __construct()
    {
        $providerDb   = Database::getProviderConnection();
        $this->dataset = new Dataset($providerDb);
    }

    public function listPublic(): array
    {
        return $this->dataset->getPublicDatasets();
    }
}
