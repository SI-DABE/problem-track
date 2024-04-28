<?php

namespace Core\Debug;

class Debugger
{
    public static function dd(): void
    {
        $str = '';
        foreach (func_get_args() as $index => $value) {
            if ($index !== 0) {
                $str .= '<hr>';
            }

            $str .= highlight_string('<?php ' . self::dump($value) . '?>', true);
        }
        echo str_replace(['&lt;?php', '?&gt;'], '', $str);
        exit;
    }

    private static function dump(mixed $value): string
    {
        ob_start();
        var_dump($value);
        return ob_get_clean();
    }
}
