<?php

require '/var/www/config/bootstrap.php';

use App\Controllers\ProblemsController;

$controller = new ProblemsController();
$controller->show();
