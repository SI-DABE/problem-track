<?php

namespace Core\Http;

class Request
{
    private string $method;
    private string $uri;

    /** @var mixed[] */
    private array $params;

    /** @var array<string, string> */
    private array $headers;

    public function __construct()
    {
        $this->method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->params = $_REQUEST;
        $this->headers = getallheaders();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    /** @return mixed[] */
    public function getParams(): array
    {
        return $this->params;
    }

    /** @return array<string, string>*/
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @param mixed[] $params*/
    public function addParams(array $params): void
    {
        $this->params = array_merge($this->params, $params);
    }
}
