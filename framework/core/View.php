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
 * Wavephp Application View Class
 *
 * 视图层类
 *
 * @package         Wavephp
 * @subpackage      core
 * @author          许萍
 *
 */

class View 
{
    public $engin;
    private $scriptPackage;

    /**
     * 初始化smarty模板引擎
     */
    public function __construct()
    {
        $dir = Wave::app()->projectPath.Wave::app()->projectName;
        $config = Wave::app()->config['smarty'];

        $smarty = new Smarty();
        $smarty->left_delimiter     = $config['left_delimiter'];
        $smarty->right_delimiter    = $config['right_delimiter'];
        $smarty->debugging          = $config['debugging'];
        $smarty->caching            = $config['caching'];
        $smarty->cache_lifetime     = $config['cache_lifetime'];
        $smarty->compile_check      = $config['compile_check'];
        $smarty->template_dir       = $dir.'/'.$config['template_dir'];
        $smarty->cache_dir          = $dir.'/'.$config['cache_dir'];
        $smarty->config_dir         = $dir.'/'.$config['config_dir'];
        $smarty->compile_dir        = Wave::app()->projectPath.$config['compile_dir'];

        WaveCommon::mkDir($smarty->compile_dir);
        if ($config['caching']) {
            WaveCommon::mkDir($smarty->config_dir);
        }

        $this->engin = $smarty;
    }

    /**
     * 设置模板引擎参数
     * @param array options
     * @return View
     */
    public function setOptions(array $options)
    {
        foreach ($options as $param => $value) {
            $this->engin->$param = $value;
        }
    }

    public function setDefaultScriptPackage($package)
    {
        $this->scriptPackage = $package;

        return $this;
    }

    /**
     * 获取默认的模板文件名
     * @return string
     *
     */
    private function getDefaultScript()
    {
        $classname = Wave::getClassName();
        $actionname = Wave::getActionName();

        return $classname.'/'.$actionname;
    }

    public function display($resource_name = '', 
                            $cache_id = null, 
                            $compile_id = null)
    {
        echo $this->fetch($resource_name, $cache_id, $compile_id, true);
    }

    public function fetch($resource_name,
                        $cache_id = null, 
                        $compile_id = null, 
                        $display = false)
    {
        if ($resource_name == '') {
            $resource_name = $this->getDefaultScript();
        }
        $resource_name = str_replace('.html', '', $resource_name).'.html';
        $fileName = $this->engin->template_dir.'/'.$resource_name;
        if (!file_exists($fileName)) {
            throw new Exception('no have file '.$fileName);
        }
        return $this->engin->fetch($resource_name, 
                                    $cache_id, 
                                    $compile_id, 
                                    $display);
    }

    public function assign($name, $val = null)
    {
        if (empty($name)) {
            return false;
        }
        $this->engin->assign($name, $val);

        return $this;
    }

    public function __set($name, $val)
    {
        $this->$name = $val;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}
?>