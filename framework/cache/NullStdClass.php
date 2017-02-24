<?php
/**
 * PHP 5.0 以上
 *
 * @package         Wavephp
 * @author          许萍
 * @copyright       Copyright (c) 2016
 * @link            https://github.com/xpmozong/wavephp2
 * @since           Version 2.0
 *
 */

/**
 * Wavephp Application NullStdClass Class
 *
 * 空缓存类
 *
 * @package         Wavephp
 * @subpackage      cache
 * @author          许萍
 *
 */
class NullStdClass
{
    public function __call($name, $arguments)
    {
        return false;
    }
    public static function __callStatic($name, $arguments)
    {
        return false;
    }
}

?>
