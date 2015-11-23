<?php
namespace Minim;

use Recoder\Recoder as SC;

class Shortcode
{
    private static $processor = null;

    public static function init()
    {
        self::$processor = new SC('{{', '}}');
    }

    public static function register($code, callable $func)
    {
        self::$processor->register($code, $func);
    }

    public static function process()
    {
        return call_user_func_array([self::$processor, 'process'], func_get_args());
    }
}
