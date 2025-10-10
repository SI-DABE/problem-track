<?php

namespace Tests;

use Core\Database\Database;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase
{
    protected $usesDatabase = false;

    public function setUp(): void
    {
        if ($this->usesDatabase) {
            Database::create();
            Database::migrate();
        }
    }

    public function tearDown(): void
    {
        if ($this->usesDatabase) {
            Database::drop();
        }
    }

    protected function getOutput(callable $callable): string
    {
        ob_start();
        $callable();
        return ob_get_clean();
    }
}
