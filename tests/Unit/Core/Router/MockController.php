<?php

namespace Tests\Unit\Core\Router;

class MockController
{
    public function action(): void
    {
        echo "Action Called";
    }
}
