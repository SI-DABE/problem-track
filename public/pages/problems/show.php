<?php
$id = intval($_GET['id']);

define('DB_PATH', '/var/www/database/problems.txt');
$problems = file(DB_PATH, FILE_IGNORE_NEW_LINES);

$problem['title'] = $problems[$id];

$title = "Visualização do Problema #{$id}";
$view = '/var/www/app/views/problems/show.phtml';

require '/var/www/app/views/layouts/application.phtml';
