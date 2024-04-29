<?php

namespace Core\Router;

use Core\Constants\Constants;

class Router
{
    private static Router|null $instance = null;
    private array $routes = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    public function dispatch(): object|bool
    {
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route) {
            if ($route->match($method, $uri)) {
                $class = $route->getControllerName();
                $action = $route->getActionName();

                $controller = new $class();
                $controller->$action();

                return $controller;
            }
        }
        return false;
    }

    public static function init()
    {
        require Constants::rootPath()->join('config/routes.php');
        Router::getInstance()->dispatch();
    }
}
