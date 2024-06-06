<?php

namespace Lib;

class StringUtils
{
    public static function camelToSnakeCase($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    public static function lowerSnakeToCamelCase($string)
    {
        $camel_case = preg_replace_callback(
            '/_([a-zA-Z])/',
            fn ($match) => strtoupper($match[1]),
            $string
        );

        return $camel_case;
    }

    public static function snakeToCamelCase($string)
    {
        return ucfirst(self::lowerSnakeToCamelCase($string));
    }
}
