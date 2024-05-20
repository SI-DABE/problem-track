<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;
use Database\Populate\ProblemsPopulate;
use Database\Populate\UsersPopulate;

Database::migrate();

ProblemsPopulate::populate();
UsersPopulate::populate();
