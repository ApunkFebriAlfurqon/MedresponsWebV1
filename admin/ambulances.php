<?php
require_once __DIR__ . '/../controllers/AmbulanceController.php';

$controller = new AmbulanceController();
$viewData = $controller->handle();
extract($viewData, EXTR_SKIP);

require __DIR__ . '/../views/admin/ambulances.php';
