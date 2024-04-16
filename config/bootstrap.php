<?php

require __DIR__ . '/../vendor/autoload.php';

use Core\Errors\ErrorsHandler;

ErrorsHandler::init();


require_once __DIR__ . '/../core/constants/general.php';

require_once ROOT_PATH . '/core/env/env.php';
require_once ROOT_PATH . '/core/debug/functions.php';
// require_once ROOT_PATH . '/core/errors/handler.php';
