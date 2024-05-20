<?php

namespace Core\Http\Middleware;

use Core\Http\Request;

interface Middleware
{
    public function handle(Request $request): void;
}
