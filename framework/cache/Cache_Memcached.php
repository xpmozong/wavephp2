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
    public $cache_name = null;

    public function __construct($came = 'memcached') 
    {
        $this->cache_name = $came;
        $this->init();
    }

    public function init() 
    {
        if (extension_loaded('memcached') == false ) {
            exit('extension memcached not found!');
        }
        $hosts = Wave::app()->config[$this->cache_name];
        $this->cacheArray[$this->cache_name] = new Memcached();

        $this->cacheArray[$this->cache_name]->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        $i = 0;
        $servers = array();
        foreach ($hosts as $key => $value) {
            $servers[$i] = array($value['host'], $value['port'], $i);
            $i++;
        }
        if (!$this->cacheArray[$this->cache_name]->addServers($servers)) {
            // throw new Exception('memcahced server '.json_encode($servers).' connection faild.');
            $this->cacheArray = null;
            $this->cache_name = null;
        }
    }

    public function getMemcached()
    {
        return $this->cacheArray[$this->cache_name];
    }

    public function set($key, $value, $lifetime = 3600) 
    {
        $lifetime = $lifetime >= 0 ? $lifetime : $this->lifetime;
        return $this->getMemcached()->set($key, $value, $lifetime);
    }

    public function get($key) 
    {
        return $this->getMemcached()->get($key);
    }

    public function increment($key, $step = 1) 
    {
        if ($this->get($key)) {
            return $this->getMemcached()->increment($key, $step);
        } else {
            return $this->set($key, 1);
        }
    }

    public function decrement($key, $step = 1) 
    {
        return $this->getMemcached()->decrement($key, $step);
    }

    public function delete($key) 
    {
        return $this->getMemcached()->delete($key);
    }
}
?>