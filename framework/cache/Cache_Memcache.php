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
 * Wavephp Application Cache_Memcache Class
 *
 * 缓存Memcache类
 *
 * @package         Wavephp
 * @subpackage      cache
 * @author          许萍
 *
 */
class Cache_Memcache implements Cache_Interface
{
    protected $pconnect = true;
    protected $lifetime = 3600;
    protected $cacheArray = array();
    protected $prefixArray = array();
    public $cacheName = null;

    public function __construct($came = 'memcache')
    {
        $this->cacheName = $came;

        if (extension_loaded('memcache') == false ) {
            exit('extension memcache not found!');
        }

        $hosts = Wave::app()->config[$this->cacheName];
        $this->cacheArray[$this->cacheName] = new Memcache();
        $this->prefixArray[$this->cacheName] = isset($hosts[0]['prefix']) ? $hosts[0]['prefix'] : '';
        $i = 1;
        foreach ($hosts as $key => $value) {
            if ($i == 1) {
                if (!@$this->cacheArray[$this->cacheName]->connect($value['host'], $value['port'])) {
                    $this->cacheArray = null;
                    $this->cacheName = null;
                }
            } else {
                $this->cacheArray[$this->cacheName]->addServer($value['host'], $value['port']);
            }
            $i++;
        }
    }

    /**
     * 选择缓存
     *
     * @return object
     *
     */
    public function getMemcache()
    {
        return $this->cacheArray[$this->cacheName];
    }

    /**
     * 选择key前缀
     *
     * @return string
     *
     */
    public function getPrefix()
    {
        return $this->prefixArray[$this->cacheName];
    }

    public function set($key, $value, $lifetime = 3600)
    {
        $lifetime = $lifetime >= 0 ? $lifetime : $this->lifetime;
        return $this->getMemcache()->set($this->getPrefix().$key, $value, false, $lifetime);
    }

    public function get($key)
    {
        return $this->getMemcache()->get($this->getPrefix().$key);
    }

    public function increment($key, $step = 1)
    {
        if ($this->get($key)) {
            return $this->getMemcache()->increment($this->getPrefix().$key, $step);
        } else {
            return $this->set($key, 1);
        }
    }

    public function decrement($key, $step = 1)
    {
        return $this->getMemcache()->decrement($this->getPrefix().$key, $step);
    }

    public function delete($key)
    {
        return $this->getMemcache()->delete($this->getPrefix().$key);
    }
}
?>
