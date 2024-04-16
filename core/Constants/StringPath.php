<?php

namespace Core\Constants;

class StringPath
{
    public function __construct(
        private string $path
    ) {
    }

    public function join(string $path): StringPath
    {

        $this->path .= '/' . ltrim($path, '/');

        return $this;
    }

    public function __toString()
    {
        return $this->path;
    }
}
