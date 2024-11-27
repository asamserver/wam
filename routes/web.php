<?php

use WHMCS\Module\Addon\kyc_verification\app\Controllers\Admin\DashboardController;
use WHMCS\Module\Addon\kyc_verification\app\Controllers\Client\DashboardController as ClientDashboardController;

$this->get('/admin/dashboard', DashboardController::class, 'index');
$this->get('/client/dashboard', ClientDashboardController::class, 'index');