<?php

namespace Lib;

class FlashMessage
{
    public static function success(string $value): void
    {
        self::message('success', $value);
    }

    public static function danger(string $value): void
    {
        self::message('danger', $value);
    }

    public static function get(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return $flash;
    }

    private static function message(string $type, string $value): void
    {
        $_SESSION['flash'][$type] = $value;
    }
}
