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
    public  $scale      = 3;
    public  $Xperiod    = 8;
    public  $Yperiod    = 7;
    public  $Xamplitude = 5;
    public  $Yamplitude = 2;
    private $img;                       // 图形资源句柄
    private $fonts      = array();      // 字体数组
    private $fontSize   = 24;           // 指定字体大小
    private $charset;                   // 随机因子

    /**
     *构造方法初始化
     */
    public function __construct()
    {
        $directory = dirname(__FILE__).'/font/';
        $mydir = dir($directory); 
        while ($file = $mydir->read()) {
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
        $codeNX = -22;
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
                        mt_rand(-30, 30),
                        $codeNX,
                        $this->fontSize*1.3,
                        $fontcolor,
                        $imagefont,
                        $this->code[$i]);
        }
    }

    /** 
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数) 
     *      
     *      高中的数学公式咋都忘了涅，写出来
     *      正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    private function _writeCurve() {
        $fontcolor = imagecolorallocate($this->img,
                                        mt_rand(1,150),
                                        mt_rand(1,150),
                                        mt_rand(1,150));

        $px = $py = 0;
        
        // 曲线前部分
        $A = mt_rand(1, $this->height/2);                  // 振幅
        $b = mt_rand(-$this->height/4, $this->height/4);   // Y轴方向偏移量
        $f = mt_rand(-$this->height/4, $this->height/4);   // X轴方向偏移量
        $T = mt_rand($this->height, $this->width*2);  // 周期
        $w = (2* M_PI)/$T;
                        
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand($this->width/2, $this->width * 0.8);  // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
                $i = (int) ($this->fontSize/5);
                while ($i > 0) {    
                    imagesetpixel($this->img, $px + $i , $py + $i, $fontcolor);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多               
                    $i--;
                }
            }
        }
        
        // 曲线后部分
        $A = mt_rand(1, $this->height/2);                  // 振幅        
        $f = mt_rand(-$this->height/4, $this->height/4);   // X轴方向偏移量
        $T = mt_rand($this->height, $this->width*2);  // 周期
        $w = (2* M_PI)/$T;      
        $b = $py - $A * sin($w*$px + $f) - $this->height/2;
        $px1 = $px2;
        $px2 = $this->width;

        for ($px = $px1; $px <= $px2; $px = $px+ 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
                $i = (int) ($this->fontSize/5);
                while ($i > 0) {            
                    imagesetpixel($this->img, $px + $i, $py + $i, $fontcolor);    
                    $i--;
                }
            }
        }
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    private function writeNoise() {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 20; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($this->img, 
                                            mt_rand(1,150), 
                                            mt_rand(1,150), 
                                            mt_rand(1,150));
            // 绘杂点
            imagestring($this->img, 
                        5, mt_rand(-10, $this->width), 
                        mt_rand(-10, $this->height), 
                        $codeSet[mt_rand(0, 29)], 
                        $noiseColor);
        }
    }

    /**
     * Wave filter
     */
    protected function waveImage()
    {
        // X-axis wave generation
        $xp = $this->scale*$this->Xperiod*rand(1, 3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width*$this->scale); $i++) {
            imagecopy($this->img, $this->img,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale*$this->Yperiod*rand(1,2);
        for ($i = 0; $i < ($this->height*$this->scale); $i++) {
            imagecopy($this->img, $this->img,
                sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }

    /**
     * Reduce the image to the final size
     */
    protected function reduceImage()
    {
        $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->img,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width, $this->height
        );
        imagedestroy($this->img);
        $this->img = $imResampled;
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
     * Cleanup
     */
    protected function cleanup()
    {
        imagedestroy($this->img);
    }

    /**
     * 对外生成
     */
    public function doimg($key = 'verifycode', $expire = 600, $useCurve = false)
    {
        // 图片宽(px)
        $this->width = $this->codelen*$this->fontSize + $this->codelen*$this->fontSize/2 + 10; 
        // 图片高(px)
        $this->height = $this->fontSize * 2;

        $this->createBg();
        $this->createCode();
        Wave::app()->session->setState($key, $this->getCode(), $expire);
        // $this->writeNoise();
        if ($useCurve) {
            $this->_writeCurve();
        }
        $this->createFont();
        // $this->waveImage();
        // $this->reduceImage();
        $this->outPut();
        $this->cleanup();
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