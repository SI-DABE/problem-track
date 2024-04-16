<?php

namespace Core\Debug;

class Debugger
{
    public static function dd()
    {
        var_dump(func_get_args());
        exit;
    }
}
