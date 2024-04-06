<?php
require '/var/www/app/models/Problem.php';

$id = intval($_GET['id']);

$problem = Problem::findById($id);

$title = "Visualização do Problema #{$id}";
$view = '/var/www/app/views/problems/show.phtml';

require '/var/www/app/views/layouts/application.phtml';
