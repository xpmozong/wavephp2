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
 * Wavephp Application VerifyCode Class
 *
 * 验证码类
 *
 * @package         Wavephp
 * @subpackage      captcha
 * @author          许萍
 *
 */
class VerifyCode
{
    private $code;                      // 验证码
    public  $codelen    = 4;            // 验证码长度
    public  $width      = 130;          // 宽度
    public  $height     = 50;           // 高度
    private $img;                       // 图形资源句柄
    private $fonts      = array();      // 字体数组
    private $fontsize   = 22;           // 指定字体大小
    private $charset;                   // 随机因子

    /**
     *构造方法初始化
     */
    public function __construct()
    {
        $dir = dirname(__FILE__).'/font/';
        $dirArr = range(1, 15);
        foreach ($dirArr as $key => $file) {
            $this->fonts[] = $dir.$file.'.ttf';
        }
    }

    /**
     * 生成随机码
     */
    private function createCode()
    {
        $this->code = '';
        $uArray = range('A', 'Z');
        $lArray = range('a', 'z');
        $nArray = range(2, 9);
        $strArray = array_merge($uArray, $lArray, $nArray);
        $this->charset = implode('', $strArray);
        $_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->code .= $this->charset[mt_rand(0,$_len)];
        }
    }

    /**
     * 生成背景
     */
    private function createBg()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocate($this->img, 
                                    mt_rand(157,255), 
                                    mt_rand(157,255), 
                                    mt_rand(157,255));
        imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
    }

    /**
     * 生成文字
     */
    private function createFont()
    {
        $_x = $this->width / $this->codelen;
        for ($i = 0; $i < $this->codelen; $i++) {
            $fontcolor = imagecolorallocate($this->img,
                                                mt_rand(0,156),
                                                mt_rand(0,156),
                                                mt_rand(0,156));
            $imgIndex = mt_rand(0,count($this->fonts)-1);
            $imagefont = $this->fonts[$imgIndex];
            imagettftext($this->img,
                        $this->fontsize,
                        mt_rand(-30,10),
                        $_x*$i+mt_rand(1,5),
                        $this->height / 1.4,
                        $fontcolor,
                        $imagefont,
                        $this->code[$i]);
        }
    }

    /**
     * 生成线条、雪花
     */
    private function createLine()
    {
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img,
                                        mt_rand(0,156),
                                        mt_rand(0,156),
                                        mt_rand(0,156));
            imageline($this->img,
                        mt_rand(0,$this->width),
                        mt_rand(0,$this->height),
                        mt_rand(0,$this->width),
                        mt_rand(0,$this->height),
                        $color);
        }
        for ($i = 0; $i < 10; $i++) {
            $color = imagecolorallocate($this->img,
                                        mt_rand(200,255),
                                        mt_rand(200,255),
                                        mt_rand(200,255));
            imagestring($this->img,
                        mt_rand(1,5),
                        mt_rand(0,$this->width),
                        mt_rand(0,$this->height),
                        '*',$color);
        }
    }

    /**
     * 输出
     */
    private function outPut()
    {
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    /**
     * 对外生成
     */
    public function doimg($key = 'verifycode', $expire = 600)
    {
        $this->createBg();
        $this->createCode();
        Wave::app()->session->setState($key, $this->getCode(), $expire);
        $this->createLine();
        $this->createFont();
        $this->outPut();
    }
    
    /**
     * 获取验证码
     *
     * @return string 验证码
     *
     */
    public function getCode()
    {
        return strtolower($this->code);
    }
}
?>