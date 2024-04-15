<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;

abstract class ControllerTestCase extends TestCase
{
    public function get(string $action, string $controller): string
    {
        $controller = new $controller();

        ob_start();
        $controller->index();
        $response = ob_get_contents();
        ob_end_clean();

        return $response;
    }
}
