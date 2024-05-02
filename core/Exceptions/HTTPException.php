<?php

namespace Core\Exceptions;

use Exception;

class HTTPException extends Exception
{
    private int $status_code;

    public function __construct(string $message, int $status_code)
    {
        parent::__construct($message);
        $this->status_code = $status_code;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }
}
