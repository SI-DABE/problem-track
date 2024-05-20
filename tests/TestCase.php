<?php

namespace Tests;

use Core\Database\Database;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase
{
    public function setUp(): void
    {
        Database::create();
        Database::migrate();
    }

    public function tearDown(): void
    {
        Database::drop();
    }

    protected function getOutput(callable $callable): string
    {
        ob_start();
        $callable();
        return ob_get_clean();
    }
}
