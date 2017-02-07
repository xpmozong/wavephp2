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
 * Wavephp Application Cache_Redis Class
 *
 * 缓存Redis类
 *
 * @package         Wavephp
 * @subpackage      cache
 * @author          许萍
 *
 */
class Cache_Redis implements Cache_Interface 
{
    protected $cacheArray = array();
    protected $prefixArray = array();
    public $cacheName = null;

    public function __construct($came = 'redis') 
    {
        $this->cacheName = $came;
        
        if (extension_loaded('redis') == false) {
            exit('extension redis not found!');
        }
        
        $hosts = Wave::app()->config[$this->cacheName];
        if (isset($hosts['slave'])) {
            $this->cacheArray[$this->cacheName] = new WaveRedisCluster(true);
            $this->prefixArray[$this->cacheName] = isset($hosts['master']['prefix']) ? $hosts['master']['prefix'] : '';
            $db = isset($hosts['master']['db']) ? $hosts['master']['db'] : 0;
            $db = $db < 0 ? 0 : $db;
            $db = $db > 15 ? 15 : $db;
            $ret1 = $this->cacheArray[$this->cacheName]->connect(array(
                                        'host'=>$hosts['master']['host'], 
                                        'port'=>$hosts['master']['port']), true, $db);
            if ($ret1) {
                foreach ($hosts['slave'] as $key => $value) {
                    $db = isset($value['db']) ? $value['db'] : 0;
                    $db = $db < 0 ? 0 : $db;
                    $db = $db > 15 ? 15 : $db;
                    $ret = $this->cacheArray[$this->cacheName]->connect(array(
                                                'host'=>$value['host'],
                                                'port'=>$value['port']), false, $db);
                    if (!$ret) {
                        $this->cacheArray = array();
                        $this->cacheName = null;
                    }
                }
            } else {
                $this->cacheArray = array();
                $this->cacheName = null;
            }
        } else {
            $this->cacheArray[$this->cacheName] = new WaveRedisCluster(false);
            $this->prefixArray[$this->cacheName] = isset($hosts['master']['prefix']) ? $hosts['master']['prefix'] : '';
            $db = isset($hosts['master']['db']) ? $hosts['master']['db'] : 0;
            $db = $db < 0 ? 0 : $db;
            $db = $db > 15 ? 15 : $db;
            $ret = $this->cacheArray[$this->cacheName]->connect(array(
                                        'host'=>$hosts['master']['host'], 
                                        'port'=>$hosts['master']['port']), true, $db);
            if (!$ret) {
                $this->cacheArray = array();
                $this->cacheName = null;
            }
        }
    }

    /**
     * 选择缓存
     *
     * @return object
     *
     */
    public function getRedis()
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

    public function set($key, $value, $lifetime = 0) 
    {
        return $this->getRedis()->set($this->getPrefix().$key, $value, $lifetime);
    }

    public function get($key) 
    {
        return $this->getRedis()->get($this->getPrefix().$key);
    }

    public function delete($key) 
    {
        return $this->getRedis()->delete($this->getPrefix().$key);
    }

    public function increment($key, $step = 1) 
    {
        return $this->getRedis()->incr($this->getPrefix().$key, $step);
    }

    public function decrement($key, $step = 1) 
    {
        return $this->getRedis()->decr($this->getPrefix().$key, $step);
    }

    //-------------------------redis操作------------------------//
    public function lpush($key, $value) 
    {
        return $this->getRedis()->lpush($this->getPrefix().$key, $value);
    }

    public function lpop($key) 
    {
        return $this->getRedis()->lpop($this->getPrefix().$key);
    }

    public function rpush($key, $value) 
    {
        return $this->getRedis()->rpush($this->getPrefix().$key, $value);
    }

    public function rpop($key) 
    {
        return $this->getRedis()->rpop($this->getPrefix().$key);
    }

    public function lget($key, $index = 0) 
    {
        return $this->getRedis()->lget($this->getPrefix().$key, $index);
    }

    public function llen($key) 
    {
        return $this->getRedis()->llen($this->getPrefix().$key);
    }

    public function sadd($key, $value)
    {
        return $this->getRedis()->sadd($this->getPrefix().$key, $value);
    }

    public function smembers($key)
    {
        return $this->getRedis()->smembers($this->getPrefix().$key);
    }

    public function sremove($key, $value)
    {
        return $this->getRedis()->sremove($this->getPrefix().$key, $value);
    }

    //-------------------------哈希操作-------------------------//
    /**
     * 将key->value写入hash表中
     * @param $hash string 哈希表名
     * @param $data array 要写入的数据 array('key'=>'value')
     */
    public function hashSet($hash, $key, $data) {
        return $this->getRedis()->hashSet($hash, $this->getPrefix().$key, $data);
    }

    /**
     * 获取hash表的数据
     * @param $hash string 哈希表名
     * @param $key mixed 表中要存储的key名 默认为null 返回所有key>value
     * @param $type int 要获取的数据类型 0:返回所有key 1:返回所有value 2:返回所有key->value
     */
    public function hashGet($hash, $key = array(), $type = 0) {
        return $this->getRedis()->hashGet($hash, $this->getPrefix().$key, $type);
    }

    /**
     * 获取hash表中元素个数
     * @param $hash string 哈希表名
     */
    public function hashLen($hash) {
        return $this->getRedis()->hashLen($hash);
    }

    /**
     * 删除hash表中的key
     * @param $hash string 哈希表名
     * @param $key mixed 表中存储的key名
     */
    public function hashDel($hash, $key) {
        return $this->getRedis()->hashDel($hash, $this->getPrefix().$key);
    }

    /**
     * 查询hash表中某个key是否存在
     * @param $hash string 哈希表名
     * @param $key mixed 表中存储的key名
     */
    public function hashExists($hash, $key) {
        return $this->getRedis()->hExists($hash, $this->getPrefix().$key);
    }
}
?>