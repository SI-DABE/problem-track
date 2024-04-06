<?php
require '/var/www/app/models/Problem.php';

$problems = Problem::all();

$title = 'Problemas Registrados';
$view = '/var/www/app/views/problems/index.phtml';

require '/var/www/app/views/layouts/application.phtml';
