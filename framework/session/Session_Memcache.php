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
 * Wavephp Application Session_Memcache Class
 *
 * SESSION Memcache类
 *
 * @package         Wavephp
 * @subpackage      session
 * @author          许萍
 *
 */

class Session_Memcache implements SessionHandlerInterface
{
    protected $lifeTime     = 86400;    // 生存周期
    protected $sess_id;
    protected $cache;

    public function __construct($config)
    {
        $this->lifeTime = $config['timeout'];
        if (!empty(Wave::app()->config['session_memcache'])) {
            if (extension_loaded('memcached')) {
                $this->cache = new Cache_Memcached('session_memcached');
            } else {
                $this->cache = new Cache_Memcache('session_memcache');
            }
            if (empty($this->cache->cacheName)) {
                exit('memcache error');
            }
        } else {
            $this->cache = Wave::app()->memcache;
        }
    }

    /**
     * 设置SESSION
     *
     * @param string $key       session关键字
     * @param string $val       session值
     *
     */
    public function setState($key, $val, $expire = 0)
    {
        // if (!isset($_SESSION)) {
        //     session_start();
        // }
        if ($expire > 0) {
            $_SESSION[$this->sess_id.$key.'_expire'] = time() + $expire;
        }

        $_SESSION[$this->sess_id.$key] = $val;
    }

    /**
     * 得到SESSION
     *
     * @param string $key       session关键字
     *
     * @return string
     *
     */
    public function getState($key)
    {
        // if (!isset($_SESSION)) {
        //     session_start();
        // }

        $txt = '';
        if (isset($_SESSION[$this->sess_id.$key])) {
            if (isset($_SESSION[$this->sess_id.$key.'_expire'])) {
                $expire = $_SESSION[$this->sess_id.$key.'_expire'];
                // 如果当前时间大于过期时间 清session
                if (time() > $expire) {
                    $_SESSION[$this->sess_id.$key.'_expire'] = 0;
                    $_SESSION[$this->sess_id.$key] = '';
                } else {
                    $txt = $_SESSION[$this->sess_id.$key];
                }
            } else {
                $txt = $_SESSION[$this->sess_id.$key];
            }
        }

        return $txt;
    }

    /**
     * 清除SESSION
     */
    public function logout($key)
    {
        // if (!isset($_SESSION)) {
        //     session_start();
        // }
        $_SESSION[$this->sess_id.$key] = '';
        unset($_SESSION[$this->sess_id.$key]);

        // session_destroy();
    }

    function open($savePath, $sessName)
    {
        return true;
    }

    function close()
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
        return true;
    }

    function read($sessID)
    {
        $this->sess_id = $sessID;
        $sessData = $this->cache->get($this->sess_id);
        if (!empty($sessData)) {
            return $sessData;
        } else {
            return '';
        }
    }

    function write($sessID, $sessData)
    {
        $this->cache->set($this->sess_id, $sessData, $this->lifeTime);
        return true;
    }

    function destroy($sessID)
    {
        // delete session-data
        $this->cache->delete($this->sess_id);
        return true;
    }

    function gc($sessMaxLifeTime)
    {
        // delete old sessions
        return true;
    }
}
?>
