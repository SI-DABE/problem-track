<?php

namespace Core\Router;

use Config\App;
use Core\Http\Middleware\Middleware;

class RouteWrapperMiddleware
{
    public function __construct(
        private string $name
    ) {
    }

    public function group(callable $callback): void
    {
        $routeSizeBefore = Router::getInstance()->getRouteSize();
        $callback();
        $routeSizeAfter = Router::getInstance()->getRouteSize();

        for ($i = $routeSizeBefore; $i < $routeSizeAfter; $i++) {
            $route = Router::getInstance()->getRoute($i);
            $route->addMiddleware($this->middlewareInstance());
        }
    }

    private function middlewareInstance(): Middleware
    {
        $class = App::$middlewareAliases[$this->name];
        return new $class();
    }
}
