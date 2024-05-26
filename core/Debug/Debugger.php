<?php

namespace Core\Debug;

class Debugger
{
    public static function dd(): void
    {
        $str = '';
        foreach (func_get_args() as $index => $value) {
            $str .= self::highlightVariableIfHTTPRequest($value, ($index !== 0));
        }
        echo str_replace(['&lt;?php', '?&gt;'], '', $str);
        exit;
    }

    public static function highlightVariableIfHTTPRequest(mixed $value, bool $hr): string
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
            $hr = $hr ? '<hr>' : '';

            return $hr . highlight_string('<?php ' . self::dump($value) . '?>', true);
        }

        return self::dump($value);
    }

    private static function dump(mixed $value): string
    {
        ob_start();
        var_dump($value);
        return ob_get_clean();
    }
}
