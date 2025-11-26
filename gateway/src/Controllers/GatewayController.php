<?php
namespace App\Controllers;

use App\Helpers\RequestForwarder;

class GatewayController {

    public function forwardToAuthService() {
        $url = getenv('AUTH_SERVICE_URL') ?? 'http://auth-service';
        RequestForwarder::forward($url);
    }

    public function forwardToDataService() {
        $url = getenv('DATA_SERVICE_URL') ?? 'http://data-service';
        RequestForwarder::forward($url);
    }
}
