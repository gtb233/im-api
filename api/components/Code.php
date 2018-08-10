<?php
/**
 * Created by PhpStorm.
 */
namespace api\components;

use yii;

class Code
{

    const VER = '1';//字典库版本

    /* 字典库 */
    protected static $_lib = array(
        // 系统提示
        '0000' => '未知错误',
        '0001' => '成功',
        '0002' => '失败',
        '0003' => '超时',
        '0004' => '访问方式错误',
        '0005' => '解密失败',
        '0006' => '重复提交',
        '0007' => '提交的数据错误',
        '0008' => '验签失败',


        // 用户提示
        '1001' => '请登录',
        '1002' => '登录成功',
        '1003' => '登录失败',
        '1004' => '账号错误或不存在',
        '1005' => '密码错误',
        '1006' => '用户存在多个账户',
        '1007' => '请输入正确的电话号码',
        '1008' => '该手机号码已注册',
        '1009' => '验证码不正确',
        '1010' => '密码格式错误，请输入大于等于6位的英文加数字',
        '1011' => '注册失败',
        '1012' => '该手机号未注册',
        '1013' => '手机号错误或密码错误',
        '1014' => '此用户已经删除或者除名，不能登陆',
        '1015' => '请重新设置支付密码',

    );

    /**
     * 检查code
     * @param $code
     * @return string
     */
    public static function c($code)
    {
        if (!array_key_exists($code, self::$_lib)) $code = '0000';
        return $code;
    }

    /**
     * 多语言翻译字典内容
     * @return mixed
     */
    public static function library()
    {
        $lib = self::$_lib;
        foreach ($lib as $key => $val) {
            $lib[$key] = \Yii::t('appCodeLib', $key);
        }
        return $lib;
    }

    /**
     * 翻译代码
     * @param $code
     * @return string
     */
    public static function learnCode($code)
    {
        $lib = self::library();
        if ($code) {
            if (!array_key_exists($code, $lib)) $code = '0000';
            return \Yii::t('appCodeLib', $lib[$code]);
        }
    }


}
