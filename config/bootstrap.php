<?php

require __DIR__ . '/../vendor/autoload.php';

use Core\Constants\Constants;
use Core\Errors\ErrorsHandler;

ErrorsHandler::init();

require_once Constants::rootPath() . '/core/env/env.php';
// require_once ROOT_PATH . '/core/debug/functions.php';
// require_once ROOT_PATH . '/core/errors/handler.php';
