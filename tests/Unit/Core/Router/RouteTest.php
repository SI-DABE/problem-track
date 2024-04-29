<?php

namespace Tests\Unit\Core\Router;

use Core\Router\Route;
use Core\Router\Router;
use Tests\TestCase;

class RouteTest extends TestCase
{
    public function test_should_create_route_using_constructor(): void
    {
        $route = new Route(method: 'GET', uri: '/', controllerName: MockController::class, actionName: 'action');

        $this->assertEquals('GET', $route->getMethod());
        $this->assertEquals('/', $route->getURI());
        $this->assertEquals(MockController::class, $route->getControllerName());
        $this->assertEquals('action', $route->getActionName());
    }

    public function test_should_add_route_to_the_router_method_get(): void
    {
        $routerReflection = new \ReflectionClass(Router::class);
        $instanceProperty = $routerReflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        // Store the original instance
        $originalInstance = $instanceProperty->getValue();

        $routerMock = $this->createMock(Router::class);
        $routerMock->expects($this->once())
            ->method('addRoute')
            ->with($this->callback(function ($route) {
                return $route instanceof Route
                    && $route->getMethod() === 'GET'
                    && $route->getUri() === '/test'
                    && $route->getControllerName() === 'TestController'
                    && $route->getActionName() === 'test';
            }));
        $instanceProperty->setValue(null, $routerMock);

        Route::get('/test', ['TestController', 'test']);

        // Restore the original instance
        $instanceProperty->setValue(null, $originalInstance);
    }

    public function test_match_should_return_true_if_method_and_uri_match(): void
    {
        $route = new Route(method: 'GET', uri: '/', controllerName: 'MockController', actionName: 'index');

        $this->assertTrue($route->match('GET', '/'));
    }

    public function test_match_should_return_false_if_method_and_uri_do_not_match(): void
    {
        $route = new Route(method: 'GET', uri: '/', controllerName: 'MockController', actionName: 'index');

        $this->assertFalse($route->match('POST', '/'));
        $this->assertFalse($route->match('GET', '/test'));
    }
}
