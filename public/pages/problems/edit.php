<?php
$id = intval($_GET['id']);

define('DB_PATH', '/var/www/database/problems.txt');
$problems = file(DB_PATH, FILE_IGNORE_NEW_LINES);

$problem['id'] = $id;
$problem['title'] = $problems[$id];

$title = "Editar Problema #{$id}";
$view = '/var/www/app/views/problems/edit.phtml';

require '/var/www/app/views/layouts/application.phtml';
