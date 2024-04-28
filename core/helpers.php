<?php

use Core\Debug\Debugger;

function dd(): void
{
    Debugger::dd(...func_get_args());
}
