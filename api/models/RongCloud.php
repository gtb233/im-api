<?php
/**
 * 融云IM处理
 * User: gaotanbin
 * Date: 2018/8/11
 * Time: 12:44
 */
namespace api\models;

use common\components\Helper;
use RongCloud\RongCloud as baseRongCloud;
use yii\base\Exception;

class RongCloud
{
    private $appkey = APP_KEY;
    private $appSecret = APP_SECRET;
    private $rongCloud;

    private $expired = 3600*24;//
    private $tokenKeyPrefix = 'yii:RongCloudToken_';

    public function __construct()
    {
        $this->rongCloud = new baseRongCloud($this->appkey, $this->appSecret);
    }

    public static function model() : RongCloud
    {
        $class = __CLASS__;
        return new $class;
    }

    /**
     * @param string $userid 用户ID
     * @param string $username 用户昵称
     * @param string $portraitUrl 用户头像
     * @param bool $isCache 是否使用缓存
     * @return string
     */
    public function rongCloudToken($userid, string $username, string $portraitUrl, $isCache = true)
    {
        $key = $this->tokenKeyPrefix . $userid;
        $token = Helper::cache()->get($key);
        if (!$token || !$isCache){
            $token = $this->rongCloud->User()->getToken($userid, $username, $portraitUrl);
            if ($token){
                $token = json_decode($token,true)['token'];
            } else {
                throw new Exception('请求TOKEN失败！');
            }
            Helper::cache()->set($key, $token, $this->expired);
        }

        return $token;
    }

}