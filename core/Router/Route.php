<?php

namespace Core\Router;

class Route
{
    public function __construct(
        private string $method,
        private string $uri,
        private $controllerName,
        private $actionName
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function match(string $method, string $uri): bool
    {
        return $this->method === $method && $this->uri === $uri;
    }

    /*
     * Static Methods
    ________________________________________*/

    public static function get(string $uri, $action)
    {
        Router::getInstance()->addRoute(new Route('GET', $uri, $action[0], $action[1]));
    }
}
