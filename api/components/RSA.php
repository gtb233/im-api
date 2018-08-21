<?php
/**
 * 加密解密类
 */
namespace api\components;

use Yii;

class RSA
{
    public $privateKey;    //私钥
    public $publicKey;  //公钥
    
    /*
     * 初始化私钥
     */
    function __construct() {
        $private = Yii::getAlias('@rsa-keyPath').'/rsa_private_key.pem';
        $public = Yii::getAlias('@rsa-keyPath') . '/rsa_public_key.pem';
    
        //私钥
        $fp = fopen($private, "r");
        $this->privateKey = fread($fp, 8192);
        fclose($fp);
    
        // 公钥,测试用
        $fp = fopen($public, "r");
        $this->publicKey = fread($fp, 8192);
        fclose($fp);
        
    }
    
    /*
     * 加密
     * 后台接口不加密数据,此函数作测试用
     */
    public function encrypt($data) {
        $res = openssl_get_publickey($this->publicKey);
        openssl_public_encrypt($data, $encrypted, $res);
        $encrypted = bin2hex($encrypted);  //转换成十六进制
        return $encrypted;
    }
    
    /*
     * 解密
     */
    public function decrypt($data) {
        $data = self::hex2bin($data);
        $res = openssl_get_privatekey($this->privateKey);
        if (@openssl_private_decrypt($data, $decrypted, $res))
            $data = $decrypted;
        else
            throw new \ErrorException('发送的数据解密失败', 400);
    
        return $data;
    }
    
    public static function hex2bin($data) {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }
}