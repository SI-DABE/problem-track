<?php

namespace Tests\Unit\Core\Http;

use Core\Constants\Constants;
use Core\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once Constants::rootPath()->join('tests/Unit/Core/Http/header_mock.php');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
    }

    public function tearDown(): void
    {
        $_REQUEST = [];
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
        unset($_SERVER['HTTP_ACCEPT']);
    }

    public function test_should_return_method(): void
    {
        $request = new Request();
        $this->assertEquals('GET', $request->getMethod());
    }

    public function test_should_return_uri(): void
    {
        $request = new Request();
        $this->assertEquals('/test', $request->getUri());
    }

    public function test_should_return_params(): void
    {
        $_REQUEST = ['name' => 'John Doe'];
        $request = new Request();
        $this->assertEquals(['name' => 'John Doe'], $request->getParams());
    }

    public function test_should_return_headers(): void
    {
        $request = new Request();
        $this->assertEquals(getallheaders(), $request->getHeaders());
    }

    public function test_add_params_should_add_the_params(): void
    {
        $request = new Request();
        $params = ['id' => 1];

        $this->assertEmpty($request->getParams());
        $request->addParams($params);

        $this->assertEquals($params, $request->getParams());

        $otherParams = ['user_id' => 1];
        $request->addParams($otherParams);

        $this->assertEquals(array_merge($params, $otherParams), $request->getParams());
    }

    public function test_accept_json_should_return_true_when_accept_Json(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $request = new Request();

        $this->assertTrue($request->acceptJson());

        $_SERVER['HTTP_ACCEPT'] = 'application/html';
        $this->assertFalse($request->acceptJson());
    }
}
