<?php

namespace Core\Router;

use Core\Constants\Constants;
use Core\Http\Request;
use Exception;

class Router
{
    private static Router|null $instance = null;
    /** @var Route[] $routes */
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

    public function addRoute(Route $route): Route
    {
        $this->routes[] = $route;
        return $route;
    }

    public function getRoutePathByName(string $name): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route->getUri();
            }
        }

        throw new Exception("Route with name $name not found", 500);
    }

    public function dispatch(): object|bool
    {
        $request = new Request();

        foreach ($this->routes as $route) {
            if ($route->match($request)) {
                $class = $route->getControllerName();
                $action = $route->getActionName();

                $controller = new $class();
                $controller->$action($request);

                return $controller;
            }
        }
        return false;
    }

    public static function init(): void
    {
        require Constants::rootPath()->join('config/routes.php');
        Router::getInstance()->dispatch();
    }
}
