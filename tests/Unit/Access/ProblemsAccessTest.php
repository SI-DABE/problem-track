<?php

namespace Tests\Access;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class ProblemsAccessTest extends TestCase
{
    private Client $client;

    public function setup(): void
    {
        parent::setUp();
        $this->client = new Client([
            'allow_redirects' => false, // Disable following redirects
        ]);
    }

    public function test_should_not_access_the_index_route_if_not_authenticated(): void
    {
        $response = $this->client->get('http://web/problems');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_should_not_access_the_show_route_if_not_authenticated(): void
    {
        $response = $this->client->get('http://web/problems/1');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_should_not_access_the_route_route_if_not_authenticated(): void
    {
        $response = $this->client->get('http://web/problems/new');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_should_not_access_the_create_route_if_not_authenticated(): void
    {
        $response = $this->client->post('http://web/problems', []);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_should_not_access_the_edit_route_if_not_authenticated(): void
    {
        $response = $this->client->get('http://web/problems/1/edit');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function test_should_not_access_the_update_route_if_not_authenticated(): void
    {
        $response = $this->client->put('http://web/problems/1', []);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }
}
