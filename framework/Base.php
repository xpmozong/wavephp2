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
 * Wavephp Application Base Class
 *
 * 基础类
 *
 * @package         Wavephp
 * @author          许萍
 *
 */
class Base
{
    public static $instance;
    private static $projectPath;        // 项目路径
    private static $projectName;        // 项目名称
    private static $modelName;          // 需要加载的模型文件夹名
    private static $hostInfo;           // 当前域名
    private static $pathInfo;           // 除域名外以及index.php
    private static $homeUrl;            // 除域名外的地址
    private static $baseUrl;            // 除域名外的根目录地址
    private static $config;             // 配置项目
    private static $defaultControl;     // 默认控制层

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 初始化
     * @param array $config 配置数组
     * @param string $mode 模式
     */
    public function init($config = null, $mode = null, $buff = array())
    {
        if (!empty($config)) {
            if (!isset($config['debuger'])) {
                $config['debuger'] = false;
            }
            self::$config = $config;
        }

        self::$projectName = !empty($config['projectName'])
                                ? $config['projectName'] : 'protected';

        self::$modelName = !empty($config['modelName'])
                            ? $config['modelName'] : 'protected';

        self::$defaultControl = !empty($config['defaultController'])
            ? $config['defaultController'] : 'site';

        $this->loadBase($mode, $buff);
    }

    /**
     * 基础设置
     * @param string $mode 模式
     */
    private function loadBase($mode = null, $buff = array())
    {
        if ($mode === 'CLI') {
            self::$pathInfo = implode('/', $buff);
            self::$projectPath = ROOT_PATH.'/';
            self::$baseUrl = '/';
            self::$homeUrl = '/';
        } else {
            $scriptArr = explode('/', $_SERVER['SCRIPT_NAME']);
            $enterFile = end($scriptArr);
            array_pop($scriptArr);
            $scriptName = implode('/', $scriptArr);
            unset($scriptArr);

            self::$projectPath = $_SERVER['DOCUMENT_ROOT'].$scriptName.'/';

            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            self::$hostInfo = isset($_SERVER['HTTP_HOST'])
                            ? $http_type.$_SERVER['HTTP_HOST'] : '';

            if ($enterFile == 'index.php') {
                $pathUrl = str_replace($scriptName, '', $_SERVER['REQUEST_URI']);
                self::$pathInfo = strtolower(str_replace($enterFile, '', $pathUrl));
                self::$homeUrl = $scriptName.'/index.php/';
            } else {
                self::$pathInfo = isset($_SERVER['PATH_INFO'])
                                ? strtolower($_SERVER['PATH_INFO'])
                                : '/'.self::$defaultControl.'/index';

                self::$homeUrl = isset($_SERVER['SCRIPT_NAME'])
                                ? strtolower($_SERVER['SCRIPT_NAME']).'/' : '/';
            }

            self::$baseUrl = $scriptName;
        }
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
        $parameter = $request = array();
        $parameter['projectPath']       = self::$projectPath;
        $parameter['projectName']       = self::$projectName;
        $parameter['modelName']         = self::$modelName;
        $parameter['homeUrl']           = self::$homeUrl;
        $parameter['defaultControl']    = self::$defaultControl;
        $parameter['config']            = self::$config;
        $request['hostInfo']            = self::$hostInfo;
        $request['pathInfo']            = self::$pathInfo;
        $request['baseUrl']             = self::$baseUrl;
        $parameter['request']           = (object) $request;
        unset($request);

        return (object) $parameter;
    }

    /**
     * 清理
     */
    public function clear()
    {
        self::$projectPath      = '';
        self::$projectName      = '';
        self::$config           = '';
        self::$hostInfo         = '';
        self::$pathInfo         = '';
        self::$homeUrl          = '';
        self::$baseUrl          = '';
        self::$defaultControl   = '';
    }

}
?>
