<?php
/**
 * Created by PhpStorm.
 * User: gaotanbin
 */
namespace api\components;

use common\components\Tool;

class Fun
{

    /**
     * 验证手机号
     * @param str $mobile
     * @return boolean
     */
    public static function isMobile($mobile)
    {
        $pattern = "/(^\d{11}$)|(^852\d{8}$)/";
        if (preg_match($pattern, $mobile))
            return true;
        else
            return false;
    }


    /**
     * 验证密码
     * @param $value
     * @return bool
     */
    public static function isPassword($value)
    {
        if (preg_match("/^[0-9a-zA-Z]{6,12}$/", $value))
            return true;
        else
            return false;
    }

    /**
     * 过滤密码
     * @param $pwd
     * @return array
     */
    public static function checkPassword($pwd)
    {
        if (preg_match("/^[0-9a-zA-Z]{6,12}$/", $pwd))
            return true;
        else
            return false;
    }


    /**
     * 验证用户名称是否合规
     * @param $value
     * @return bool
     */
    public static function isUsername($value)
    {
        if (preg_match("/^(GW|gw|Gw|gW)[0-9]{7,15}$/", $value))
            return true;
        else
            return false;
    }

    /**
     * url化参数
     * @param $array
     * @return string
     */
    public static function plain($array)
    {
        ksort($array);
        $plain = '';
        foreach ($array as $k => $v) {
            $plain .= $k . '=' . $v . '&';
        }
        return substr($plain, 0, -1);
    }

    /**
     * 图片地址转换
     * (用于输出时添加域名,支持图片数组)
     * (注：应APP要求，应对返回数据做格式区分，$imgPath 参数为空时应设置正确对应数据类型)
     * @user tanbin.gao
     * @date 2017-5-26
     * @param string | array $imgPath 图片地址 (添加是否存在http://重复验证)
     * @param bool $absoluteUrl 是否绝对地址
     * @param bool $isThumbnail 是否缩略图
     * @param string $params 开启缩略图时使用 宽度*高度 例：100*100
     * @return string
     */
    public static function imageUrlConvert($imgPath, $absoluteUrl = true, $isThumbnail = false, $params = '')
    {
        if (empty($imgPath)){
            if (is_array($imgPath)){
                return [];
            }
            return '';
        }
        if ($absoluteUrl) {
            if (is_array($imgPath)) {
                foreach ($imgPath as $key => $item) {
                    $imgPath[$key] = self::imageUrlConvert($item,$absoluteUrl,$isThumbnail,$params);
                }
                return $imgPath;
            } else if (is_string($imgPath)) {
                if ($isThumbnail && $params != ''){
                    $imgPath = Tool::showImg($imgPath, $params);
                }
                if (stripos($imgPath,"http:/") === FALSE)
                    return IMG_DOMAIN . DS . $imgPath;
                return $imgPath;
            } else {
                return "";
            }
        } else {
            if ($isThumbnail && $params != ''){
                $imgPath = Tool::showImg($imgPath, $params);
            }
            return $imgPath;
        }
    }

}