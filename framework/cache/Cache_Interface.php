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
 * Wavephp Application Cache_Interface Class
 *
 * 缓存接口类
 *
 * @package         Wavephp
 * @subpackage      cache
 * @author          许萍
 *
 */
interface Cache_Interface
{
    /**
     * 获取值
     */
    public function get($id);

    /**
     * 设置值
     */
    public function set($id, $data, $lifetime = 3600);

    /**
     * 删除值
     */
    public function delete($id);

    /**
     * 增加值
     */
    public function increment($id, $step = 1);

    /**
     * 删除值
     */
    public function decrement($id, $step = 1);
}
?>
