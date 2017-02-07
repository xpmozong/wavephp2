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
 * Wavephp Application WaveCommon Class
 *
 * 框架公共类
 *
 * @package         Wavephp
 * @subpackage      core
 * @author          许萍
 *
 */
class WaveCommon
{
    /**
     * 发送邮件函数
     *
     * @param string $to            发送对方  多个以英文逗号隔开
     * @param string $subject       标题
     * @param string $body          内容
     * @param string $fromName      来源名称 默认为wavephp
     * @param string $attachment    可以添加附件，绝对路径 默认为空 多个附件以英文逗号隔开
     * @param bool $isHTML          是否为html页 默认为true
     * @param int $wordWrap         设置每行字符串的长度 默认为80
     * @param string $charSet       设置邮件的字符编码，默认为UTF-8
     * @param int $SMTPAuth         开启认证 默认为true
     * 
     * @return bool true为成功
     *
     */
    public static function sendMail($to         = '', 
                                    $subject    = '', 
                                    $body       = '',
                                    $fromName   = 'wavephp',
                                    $attachment = '',
                                    $isHTML     = true,
                                    $wordWrap   = 80,
                                    $charSet    = 'UTF-8',
                                    $SMTPAuth   = true)
    {
        $mail_config = Wave::app()->config['mail_config'];
        try {
            $mail = new PHPMailer(true); 
            $mail->IsSMTP();
            $mail->CharSet    = $charSet;   // 设置邮件的字符编码，这很重要，不然中文乱码
            $mail->SMTPAuth   = $SMTPAuth;  // 开启认证
            $mail->Port       = $mail_config['port'];
            $mail->Host       = $mail_config['host'];
            $mail->Username   = $mail_config['username'];
            $mail->Password   = $mail_config['password'];
            $mail->From       = $mail_config['username'];
            $mail->FromName   = $fromName;
            $mail->Subject    = $subject;
            $mail->Body       = $body;
            $mail->WordWrap   = $wordWrap;
            $toArr = explode(',', $to);
            foreach ($toArr as $key => $toUser) {
                $mail->AddAddress($toUser);
            }
            if (!empty($attachment)) {
                $attachArr = explode(',', $attachment);
                foreach ($attachArr as $key => $attach) {
                    $mail->AddAttachment($attach);
                }
            }
            $mail->IsHTML(true); 
            $mail->Send();
            
            return true;
        } catch (phpmailerException $e) {
            return 'send mail failure: '.$e->errorMessage();
        }
    }

    /**
     * curl
     *
     * @param string    $url        地址
     * @param string    $method     方法
     * @param array     $data       提交数组
     * @param int       $timeout    超时时间
     *
     * @return string or false
     *
     */
    public static function curl($url = '', $method = 'GET', $data = array(), $timeout = 60) 
    {
        $ch = curl_init();
        if (strtoupper($method) == 'GET' && $data) {
            $postdata = http_build_query($data, '', '&');
            $url .= '?'.$postdata;
        } elseif (strtoupper($method) == 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif (strtoupper($method) == 'JSON' && $data) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $response = false;
        }

        curl_close($ch);

        return $response;
    }

    /**
     * curl
     *
     * @param string    $url        地址
     * @param string    $method     方法
     * @param array     $data       提交数组
     * @param int       $timeout    超时时间
     *
     * @return array("status"=>ture|false, "data"=>"", "error"=>"", "http_status"=>200);
     *
     */
    public static function wcurl(   $url        = '', 
                                    $method     = 'GET', 
                                    $data       = array(), 
                                    $timeout    = 60, 
                                    $header     = array('Content-type:application/json')) 
    {
        $result = array('status' => false,
                        'http_status' => 0,
                        'data' => '',
                        'error' => '');
        try{

            $ch = curl_init();
            if (strtoupper($method) == 'GET' && $data) {
                $postdata = http_build_query($data, '', '&');
                $url .= '?'.$postdata;
            } elseif (strtoupper($method) == 'POST' && $data) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } elseif (strtoupper($method) == 'JSON' && $data) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $contents = curl_exec($ch);
            $response = curl_getinfo($ch);           
            $result['status'] = true;
            $result['http_status'] = $response['http_code'];
            $result['data'] = $contents;

            // 出错
            if (curl_errno($ch)) {
                $result['error'] = curl_error($ch);
            }
            curl_close ($ch);

        } catch(Exception $ex) {
            $result['status'] = false;
            $result['error'] = $ex;
        }
    
        return $result;
    }

    /**
     * 发起http异步请求 无需等待结果
     * @param string $url http地址
     * @param string $method 请求方式
     * @param array $params 参数
     * @param string $ip 支持host配置
     * @param int $connectTimeout 连接超时，单位为秒
     * @throws Exception
     */
    public static function wexec($url, $method = 'GET', $params = array(), $ip = null, $connectTimeout = 1)
    {
        $method_get = 'GET';
        $method_post = 'POST';

        $urlInfo = parse_url($url);
        $host = $urlInfo['host'];
        $port = isset($urlInfo['port']) ? $urlInfo['port'] : 80;
        $path = isset($urlInfo['path']) ? $urlInfo['path'] : '/';
        !$ip && $ip = $host;

        $method = strtoupper(trim($method)) !== $method_post ? $method_get : $method_post;
        $params = http_build_query($params);

        if ($method === $method_get && strlen($params) > 0) {
            $path .= '?' . $params;
        }
        $fp = fsockopen($ip, $port, $errorCode, $errorInfo, $connectTimeout);
        
        if ($fp === false) {
            throw new Exception('Connect failed , error code: ' . $errorCode . ', error info: ' . $errorInfo);
        } else {
            $http  = "$method $path HTTP/1.1\r\n";
            $http .= "Host: $host\r\n";
            $http .= "Content-type: application/x-www-form-urlencoded\r\n";
            $method === $method_post && $http .= "Content-Length: " . strlen($params) . "\r\n";
            $http .= "\r\n";
            $method === $method_post && $http .= $params . "\r\n\r\n";

            if (fwrite($fp, $http) === false || fclose($fp) === false) {
                throw new Exception('Request failed.');
            }
        }
    }

    /**
     * 获得日期
     * @return string 日期
     */
    public static function getDate()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 获得年月
     * @return string 日期
     */
    public static function getYearMonth()
    {
        return date('Ym');
    }

    /**
     * 获得图片格式数组
     * @return array
     */
    public static function getImageTypes()
    {
        return array(
                    'image/jpeg','image/jpg','image/pjpeg',
                    'image/gif','image/png','image/x-png',
                    'image/bmp','image/x-ms-bmp'
                );
    }

    /**
     * 加转义
     * @param array $data   需过滤的数组
     * @return array        过滤数组
     */
    public static function getFilter($data)
    {
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            $data[$key][$k] = self::getFilter($v);
                        } else {
                            $data[$key][$k] = addslashes($v);
                        }
                    }
                } else {
                    $data[$key] = addslashes($value);
                }
            }
        }

        return $data;
    }

    /**
     * 输出结果
     * @param bool $code        错误码
     * @param string $msg       信息
     */
    public static function exportResult($code, $msg, $data = array(), $callback = null)
    {
        $json_array = array();
        $json_array['code'] = $code;
        $json_array['msg'] = $msg;
        $json_array['data'] = $data;
        if (!empty($callback)) {
            echo $callback.'('.json_encode($json_array).')';
        } else {
            echo json_encode($json_array);
        }
        unset($json_array);die;
    }

    /** 
     * 循环创建目录
     * 
     * @param string $dir 文件夹
     * @param $mode 文件夹权限
     *
     * @return bool
     * 
     */ 
    public static function mkDir($dir, $mode = 0777) 
    { 
        if ($dir == '') return true;
        if (is_dir($dir) || @mkdir($dir,$mode)) return true; 
        if (!WaveCommon::mkDir(dirname($dir),$mode)) return false;

        return @mkdir($dir,$mode); 
    }
    
}
?>
