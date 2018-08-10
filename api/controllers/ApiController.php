<?php
/**
 * Created by PhpStorm.
 * User: gaotanbin
 * Date: 2018/8/10
 * Time: 16:51
 */

namespace api\controllers;

use api\components\Code;
use common\components\RateLimiter;
use yii\base\Controller;

class ApiController extends Controller
{
    protected $prefix = 'Yii-im';
    protected $expire = '86400';

    public function behaviors()
    {
        $behaviors = parent::behaviors(); // TODO: Change the autogenerated stub
        $behaviors['rateLimiter'] = [
            'class' => RateLimiter::class,
            'enableRateLimitHeaders' => false,
            'rateLimitPost' => [2, 1], //一秒10次
            'rateLimitGet' => [100, 60]
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        define('CURRENT_VERSION', $this->module->id); // 设置进入版本
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function getResult($resultCode, $data, $type = '')
    {
        $result = [
            'resultCode' => Code::c($resultCode),
            'resultDesc' => '',
            'resultData' => '',
            'actionType' => $type,
        ];
        if (is_string($data) || is_numeric($data)) {
            $result['resultDesc'] = $data;
        } else {
            $result['resultData'] = $data;
        }

        return ['response' => $result];
    }

    public function _success($data = [], $type = '')
    {
        header('Content-Type: application/json');
        $resultDate = json_encode($this->getResult('0001', $data, $type), JSON_UNESCAPED_UNICODE);

        echo $resultDate;
        exit();
    }

    public function _error($resultCode, $resultDesc = '', $type = '')
    {
        header('Content-Type: application/json');
        $resultDate = json_encode($this->getResult($resultCode, Code::learnCode($resultCode), $type), JSON_UNESCAPED_UNICODE);
        // debug 记录其他信息

        echo $resultDate;
        exit();
    }


}

