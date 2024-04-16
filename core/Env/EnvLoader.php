<?php

namespace Core\Env;

use Core\Constants\Constants;

class EnvLoader
{
    public static function init(): void
    {
        $envs = parse_ini_file(Constants::rootPath()->join('.env'));

        foreach ($envs as $key => $value) {
            $_ENV[$key] = $value;
        }
    }
}
