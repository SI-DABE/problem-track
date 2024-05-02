<?php

use Core\Debug\Debugger;
use Core\Router\Router;

if (!function_exists('d')) {
    function dd(): void
    {
        Debugger::dd(...func_get_args());
    }
}

if (!function_exists('route')) {
    /**
     * @param string $name
     * @param mixed[] $params
     * @return string
     */
    function route(string $name, $params = []): string
    {
        return Router::getInstance()->getRoutePathByName($name, $params);
    }
}
