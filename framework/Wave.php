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
 * Wavephp Application Wave Class
 *
 * 框架入口
 *
 * @package         Wavephp
 * @author          许萍
 *
 */

define('Application', true);
define('START_TIME', microtime(TRUE));

if (function_exists('memory_get_usage'))
{
    define('MEMORY_USAGE_START', memory_get_usage());
}

class Wave
{
    public $Base                = null;
    public static $app          = array();
    public static $_debug       = array();
    public static $mode         = null;
    public static $cliParams    = array();
    public static $output       = null; // 输出
    public static $Route;
    // public static $cookie;
    // public static $session;
    // public static $redis;
    // public static $memcache;

    /**
     * 初始化
     * @param string $configfile 配置文件
     * @param string $mode 模式
     */
    public function __construct($configfile = null, $mode = null, $buff = array(), $params = array())
    {
        global $config;
        if (empty($config) && file_exists($configfile)) {
            require $configfile;
        }
        self::$mode = $mode;
        self::$cliParams = $params;
        $this->Base = Base::getInstance();
        $this->Base->init($config, $mode, $buff, $params);
        self::$app = $this->Base->app();

        $this->loadIniSet();
        $this->loadMemcache();
        $this->loadRedis();
        $this->loadSession();
        $this->loadCookie();
    }

    /**
     * 开始
     */
    public function run()
    {
        self::$Route = new Route();
        self::$Route->route();
        $this->Base->clear();
    }

    /**
     * ini_set
     */
    public function loadIniSet()
    {
        if (!empty(Wave::app()->config['ini_set'])) {
            $ini_setArr = Wave::app()->config['ini_set'];
            foreach ($ini_setArr as $key => $value) {
                ini_set($key, $value);
            }
        }
    }

    /**
     * memcache 连接
     */
    private function loadMemcache()
    {
        if (empty(self::$app->memcache)){
            if (!empty(self::$app->config['memcache'])){
                if (extension_loaded('memcached')){
                    self::$app->memcache = new Cache_Memcached();
                } else {
                    self::$app->memcache = new Cache_Memcache();
                }
                if (empty(self::$app->memcache->cacheName)) {
                    exit('memcache error');
                }
            }
        }
    }

    /**
     * redis 连接
     */
    private function loadRedis()
    {
        if (empty(self::$app->redis)){
            if (!empty(self::$app->config['redis'])){
                self::$app->redis = new Cache_Redis();
                if (empty(self::$app->redis->cacheName)) {
                    exit('redis error');
                }
            }
        }
    }

    /**
     * SEESION
     */
    private function loadSession()
    {
        if (!empty(self::$app->config)){
            if (isset(self::$app->config['session'])){
                $config = Wave::app()->config['session'];
                $class = 'Session_'.ucfirst($config['driver']);
                $session = new $class($config);
                session_set_save_handler(array(&$session,'open'),
                             array(&$session,'close'),
                             array(&$session,'read'),
                             array(&$session,'write'),
                             array(&$session,'destroy'),
                             array(&$session,'gc'));

                self::$app->session = $session;
            }
        }
    }

    /**
     * COOKIE
     */
    private function loadCookie()
    {
        if (!empty(self::$app->config)){
            if (isset(self::$app->config['cookie'])){
                self::$app->cookie = new CookieModule();
            }
        }
    }

    /**
     * memcache 使用
     */
    public static function useMemcache()
    {
        if (self::$memcache) {
            return self::$memcache;
        }
        if (extension_loaded('memcached')){
            self::$memcache = new Cache_Memcached();
        } else {
            self::$memcache = new Cache_Memcache();
        }

        if (empty(self::$memcache->cacheName)) {
            exit('memcache error');
        }

        return self::$memcache;
    }

    /**
     * redis 使用
     */
    public static function useRedis()
    {
        if (self::$redis) {
            return self::$redis;
        }
        self::$redis = new Cache_Redis();
        if (empty(self::$redis->cacheName)) {
            exit('redis error');
        }

        return self::$redis;
    }

    /**
     * SESSION 使用
     */
    public static function useSession()
    {
        if (self::$session) {
            return self::$session;
        }
        $config = Wave::app()->config['session'];
        $class = 'Session_'.ucfirst($config['driver']);
        self::$session = new $class($config);
        session_set_save_handler(array(&self::$session,'open'),
                     array(&self::$session,'close'),
                     array(&self::$session,'read'),
                     array(&self::$session,'write'),
                     array(&self::$session,'destroy'),
                     array(&self::$session,'gc'));

        return self::$session;
    }

    /**
     * COOKIE 使用
     */
    public function useCookie()
    {
        if (self::$cookie) {
            return self::$cookie;
        }
        self::$cookie = new CookieModule();

        return self::$cookie;
    }

    /**
     * 一些公共参数，供项目调用的
     *
     * 例如在项目中输出除域名外的根目录地址 Wave::app()->homeUrl;
     *
     * @return object array
     *
     */
    public static function app()
    {
        return self::$app;
    }

    /**
     * 记录系统 Debug 事件
     *
     * 打开 debug 功能后相应事件会在页脚输出
     *
     * @param string $type
     * @param string $expend_time
     * @param string $message
     */
    public static function debug_log($type, $expend_time, $message)
    {
        self::$_debug[$type][] = array(
            'expend_time' => $expend_time,
            'log_time' => microtime(TRUE),
            'message' => $message
        );
    }

    /**
     * 获取控制器名
     */
    public static function getClassName()
    {
        return self::$Route->getClassName();
    }

    /**
     * 获取控制器方法名
     */
    public static function getActionName()
    {
        return self::$Route->getActionName();
    }

    /**
     * 设置内容
     */
    public function setBody($content)
    {
        Wave::$output = $content;
    }

    /**
     * 读出内容
     */
    public static function getBody()
    {
        if (self::$mode === 'CLI') {
            return self::$output;
        } else {
            echo self::$output;
        }
    }

    /**
     * 写入缓存文件
     *
     * @param string $filepath 文件地址
     * @param string $content 内容
     * @param string $mod 写入类型 默认 w
     *
     * @return int $strlen 写入字符长度
     *
     */
    public static function writeCache($filepath, $content, $mod = 'w')
    {
        $fp = fopen($filepath, $mod);
        flock($fp, LOCK_EX);
        $strlen = fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $strlen;
    }

    /**
     * 读缓存文件
     *
     * @param string $filepath 文件地址
     *
     * @return string 内容
     *
     */
    public static function readCache($filepath)
    {
        if (file_exists($filepath)) {
            return file_get_contents($filepath);
        }

        return '';
    }
}

?>
