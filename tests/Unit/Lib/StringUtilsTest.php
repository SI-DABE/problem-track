<?php

namespace Tests\Unit\Lib;

use Lib\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public function test_camel_to_snake_case(): void
    {
        $this->assertEquals('my_test_string', StringUtils::camelToSnakeCase('myTestString'));
        $this->assertEquals('another_test_string', StringUtils::camelToSnakeCase('AnotherTestString'));
        $this->assertEquals('test', StringUtils::camelToSnakeCase('Test'));
    }

    public function test_lower_snake_to_camel_case(): void
    {
        $this->assertEquals('myTestString', StringUtils::lowerSnakeToCamelCase('my_test_string'));
        $this->assertEquals('anotherTestString', StringUtils::lowerSnakeToCamelCase('another_test_string'));
        $this->assertEquals('test', StringUtils::lowerSnakeToCamelCase('test'));
    }

    public function test_snake_to_camel_case(): void
    {
        $this->assertEquals('MyTestString', StringUtils::snakeToCamelCase('my_test_string'));
        $this->assertEquals('AnotherTestString', StringUtils::snakeToCamelCase('another_test_string'));
        $this->assertEquals('Test', StringUtils::snakeToCamelCase('test'));
    }
}
