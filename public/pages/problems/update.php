<?php

$method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];

if ($method !== 'PUT') {
    header('Location: /pages/problems');
    exit;
}

$problem = $_POST['problem'];

$id = $problem['id'];
$title = trim($problem['title']);

$errors = [];

if (empty($title))
    $errors['title'] = 'não pode ser vazio!';


if (empty($errors)) {
    define('DB_PATH', '/var/www/database/problems.txt');

    $problems = file(DB_PATH, FILE_IGNORE_NEW_LINES);
    $problems[$id] = $title;

    $data = implode(PHP_EOL, $problems);
    file_put_contents(DB_PATH, $data . PHP_EOL);

    header('Location: /pages/problems');
} else {
    $title = "Editar Problema #{$id}";
    $view = '/var/www/app/views/problems/edit.phtml';

    require '/var/www/app/views/layouts/application.phtml';
}
