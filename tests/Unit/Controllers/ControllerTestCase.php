<?php

namespace Tests\Unit\Controllers;

use Core\Constants\Constants;
use Core\Http\Request;
use Tests\TestCase;

abstract class ControllerTestCase extends TestCase
{
    private Request $request;

    public function setUp(): void
    {
        parent::setUp();
        require Constants::rootPath()->join('config/routes.php');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $this->request = new Request();
    }

    public function tearDown(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function get(string $action, string $controllerName, array $params = []): string
    {
        return $this->execController($action, $controllerName, $params);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function post(string $action, string $controllerName, array $params = []): string
    {
        return $this->execController($action, $controllerName, $params);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function put(string $action, string $controllerName, array $params = []): string
    {
        return $this->execController($action, $controllerName, $params);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function execController(string $action, string $controllerName, array $params = []): string
    {
        $controller = $this->getControllerInstance($controllerName);
        $this->request->addParams($params);

        ob_start();
        try {
            $controller->$action($this->request);
            return ob_get_contents();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            ob_end_clean();
        }
    }


    private function getControllerInstance(string $controllerName) // @phpstan-ignore-line
    {
        if (!class_exists('\OverriddenController')) {
            // This is necessary to override redirectTo, because the redirectTo
            // method from the Controller call the exit and stop the test execution
            $code = "
            class OverriddenController extends $controllerName {
                protected function redirectTo(string \$location): void {
                    echo 'Location: ' . \$location;
                }
            }
            ";

            eval($code);
        }

        return new \OverriddenController(); // @phpstan-ignore-line
    }
}
