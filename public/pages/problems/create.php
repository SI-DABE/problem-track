<?php

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    header('Location: /pages/problems');
    exit;
}

$problem = $_POST['problem'];
$title = trim($problem['title']);

$errors = [];

if (empty($title))
    $errors['title'] = 'não pode ser vazio!';


if (empty($errors)) {
    define('DB_PATH', '/var/www/database/problems.txt');
    file_put_contents(DB_PATH, $title . PHP_EOL, FILE_APPEND);

    header('Location: /pages/problems');
} else {
    $title = 'Novo Problema';
    $view = '/var/www/app/views/problems/new.phtml';

    require '/var/www/app/views/layouts/application.phtml';
}
