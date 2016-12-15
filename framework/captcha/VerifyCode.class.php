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
    private $fontSize   = 26;           // 指定字体大小
    private $charset;                   // 随机因子

    /**
     *构造方法初始化
     */
    public function __construct()
    {
        $directory = dirname(__FILE__).'/font/';
        $mydir = dir($directory); 
        while($file = $mydir->read()) {
            if (($file != ".") && ($file != "..")) {
                $this->fonts[] = $directory.$file;
            }
        } 
        $mydir->close();
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
        $color = imagecolorallocate($this->img, 243, 251, 254);
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    /**
     * 生成文字
     */
    private function createFont()
    {
        $_x = $this->width / $this->codelen;
        $codeNX = 0;
        $fontcolor = imagecolorallocate($this->img,
                                        mt_rand(1,150),
                                        mt_rand(1,150),
                                        mt_rand(1,150));
        for ($i = 0; $i < $this->codelen; $i++) {
            $imgIndex = mt_rand(0,count($this->fonts)-1);
            $imagefont = $this->fonts[$imgIndex];
            $codeNX  += mt_rand($this->fontSize*1.2, $this->fontSize*1.6);
            imagettftext($this->img,
                        $this->fontSize,
                        mt_rand(-40, 40),
                        $codeNX,
                        $this->fontSize*1.6,
                        $fontcolor,
                        $imagefont,
                        $this->code[$i]);
        }
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    private function writeNoise() {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for($i = 0; $i < 8; $i++){
            //杂点颜色
            $noiseColor = imagecolorallocate($this->img, mt_rand(100,150), mt_rand(100,150), mt_rand(100,150));
            for($j = 0; $j < 5; $j++) {
                // 绘杂点
                imagestring($this->img, 5, mt_rand(-10, $this->width),  mt_rand(-10, $this->height), $codeSet[mt_rand(0, 29)], $noiseColor);
            }
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
        // 图片宽(px)
        $this->width = $this->codelen*$this->fontSize*1.2 + $this->codelen*$this->fontSize/2; 
        // 图片高(px)
        $this->height = $this->fontSize * 2.5;

        $this->createBg();
        $this->createCode();
        Wave::app()->session->setState($key, $this->getCode(), $expire);
        $this->writeNoise();
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