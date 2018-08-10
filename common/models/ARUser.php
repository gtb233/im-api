<?php
/**
 * 基础用户信息类-速率限制
 * Date: 2017/12/22
 * Time: 14:04
 */

namespace common\models;

use yii;
use common\components\Helper;
use yii\db\ActiveRecord;

class ARUser extends ActiveRecord implements \yii\filters\RateLimitInterface
{
    public $rateLimitGet = [100,60]; //60秒内最多只请get请求100次
    public $rateLimitPost = [10,1]; //1秒内只能提交10次

    /**
     * 以下三个方法用于速率限制
     */
    /**
     * Returns the maximum number of allowed requests and the window size.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     * 在单位时间内允许的请求的最大数目，例如，[10, 60] 表示在60秒内最多请求10次。
     */
    public function getRateLimit($request, $action){
        return $request->getIsPost() ? $this->rateLimitPost : $this->rateLimitGet;
    }

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action){
        $method = $request->getIsPost() ? 'p':'g';
        $timeStr = yii::$app->cache->get('yii-RatLimit'.$method.Helper::getIP());
        if($timeStr){
            return explode('|',$timeStr);
        }else{
            return $method=='g'? [$this->rateLimitGet[0],time()] : [$this->rateLimitPost[0],time()];
        }
    }

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @param integer $allowance the number of allowed requests remaining.
     * @param integer $timestamp the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp){
        $method = $request->getIsPost() ? 'p':'g';
        yii::$app->cache->set('yii-RatLimit'.$method.Helper::getIP(),$allowance.'|'.$timestamp,600);
    }
}