<?php

$envs = parse_ini_file('/var/www/.env');

foreach ($envs as $key => $value) {
    $_ENV[$key] = $value;
}
