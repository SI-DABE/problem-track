<?php

use Core\Debug\Debugger;

function dd()
{
    Debugger::dd(...func_get_args());
}
