<?php

$method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];

if ($method !== 'DELETE') {
    header('Location: /pages/problems');
    exit;
}

$problem = $_POST['problem'];

$id = $problem['id'];

define('DB_PATH', '/var/www/database/problems.txt');

$problems = file(DB_PATH, FILE_IGNORE_NEW_LINES);
unset($problems[$id]);

$data = implode(PHP_EOL, $problems);
file_put_contents(DB_PATH, $data . PHP_EOL);

header('Location: /pages/problems');
