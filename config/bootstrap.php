<?php

require __DIR__ . '/../vendor/autoload.php';

use Core\Env\EnvLoader;
use Core\Errors\ErrorsHandler;

ErrorsHandler::init();
EnvLoader::init();

// require_once ROOT_PATH . '/core/debug/functions.php';
