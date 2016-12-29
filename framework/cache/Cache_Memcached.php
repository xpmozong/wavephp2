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
 * Wavephp Application Cache_Memcached Class
 *
 * 缓存Memcache类
 *
 * @package         Wavephp
 * @subpackage      cache
 * @author          许萍
 *
 */
class Cache_Memcached implements Cache_Interface 
{
    public $pconnect = true;
    public $lifetime = 3600;
    protected $cacheArray = array();
    protected $prefixArray = array();
    public $cacheName = null;

    public function __construct($came = 'memcached') 
    {
        $this->cacheName = $came;
        $this->init();
    }

    /**
     * 初始化
     */
    public function init() 
    {
        if (extension_loaded('memcached') == false ) {
            exit('extension memcached not found!');
        }
        $hosts = Wave::app()->config[$this->cacheName];
        $this->cacheArray[$this->cacheName] = new Memcached();
        $this->prefixArray[$this->cacheName] = isset($hosts[0]['prefix']) ? $hosts[0]['prefix'] : '';
        $this->cacheArray[$this->cacheName]->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        $i = 0;
        $servers = array();
        foreach ($hosts as $key => $value) {
            $servers[$i] = array($value['host'], $value['port'], $i);
            $i++;
        }
        if (!$this->cacheArray[$this->cacheName]->addServers($servers)) {
            $this->cacheArray = array();
            $this->cacheName = null;
        }
    }

    /**
     * 选择缓存
     *
     * @return object
     *
     */
    public function getMemcached()
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
        return $this->getMemcached()->set($this->getPrefix().$key, $value, $lifetime);
    }

    public function get($key) 
    {
        return $this->getMemcached()->get($this->getPrefix().$key);
    }

    public function increment($key, $step = 1) 
    {
        if ($this->get($key)) {
            return $this->getMemcached()->increment($this->getPrefix().$key, $step);
        } else {
            return $this->set($key, 1);
        }
    }

    public function decrement($key, $step = 1) 
    {
        return $this->getMemcached()->decrement($this->getPrefix().$key, $step);
    }

    public function delete($key) 
    {
        return $this->getMemcached()->delete($this->getPrefix().$key);
    }
}
?>