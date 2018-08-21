<?php
/**
 * Created by PhpStorm.
 * User: gaotanbin
 * Date: 2018/8/10
 * Time: 23:33
 */

namespace api\modules\v1\controllers;

use api\components\Fun;
use api\controllers\ApiController;
use api\models\ApiUserExtension;
use api\models\RongCloud;
use api\models\Users;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

class UserController extends ApiController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::class, #权限
                'only' => ['register'],
                'denyCallback' => function ($rule, $action) {
                    $this->_error('0000', '权限不足');
                },
                'rules' => [
                    [
                        'actions' => ['register'],
                        'allow' => true,
                        //'roles' => ['@'],
                        'ips' => ['127.0.0.1'],
                        //'matchCallback' => function ($rule, $action) {
                        //    return date('Y-m-d') === '2018-10-1';  #设置规则只有10月1日可访问此
                        //}
                    ],
                ],
            ],
        ], parent::behaviors());
    }

    /**
     * 用户登录
     */
    public function actionLogin()
    {
        $this->_error('0000', '未启用');
    }

    /**
     * 注册--生成测试数据
     */
    public function actionRegister()
    {
        try {
            $user = new Users();
            $user->username = 'GW' . $this->randomStr(10);
            $user->setPassword('123456');
            if ($user->save()) {
                $user->username = 'GW' . substr('00000000', 0, 8 - strlen($user->id)) . $user->id;
                $user->save();
                //保存其他信息
                $userExtension = new ApiUserExtension();
                $userExtension->user_id = $user->id;
                $userExtension->nickname = $this->randomStr(5);
                $userExtension->head_portrait = 'http://www.gt233.cn/wp-content/uploads/logo.png';
                if (!$userExtension->save()) {
                    throw new Exception(current(current($userExtension->errors)));
                }

            } else {
                throw new Exception(current(current($user->errors)));
            }

            $this->_success();
        } catch (Exception $e) {
            $this->_error('1011', $e->getMessage());
        }
    }

    public function actionList()
    {
        try {
            $userList = Users::model()->getUserList();
            $this->_success($userList);
        } catch (Exception $e) {
            $this->_error('0000');
        }

    }

    /**
     * 取得用户信息
     */
    public function actionInfo()
    {
        try {
            $username = $this->getPost('code');
            $userInfo = Users::model()->getUserInfoByUsername($username);
            if ($userInfo) {
                $this->_success($userInfo);
            }

            $this->_error('1004');
        } catch (Exception $e) {
            $this->_error('0000', $e->getMessage());
        }
    }

    /**
     * 取得融云TOKEN
     */
    public function actionToken()
    {
        try {
            $fromgw = $this->getPost('fromgw'); //用户
            $togw = $this->getPost('togw'); //接收对象

            if (!Fun::isUsername($fromgw['GW']) || !Fun::isUsername($togw['GW'])) {
                $this->_error('0007');
            }

            $userList = Users::model()->getUserList();
            if (isset($userList[0]['username'])) $userList = array_column($userList, 'username');
            if (!in_array($fromgw['GW'], $userList) || !in_array($togw['GW'], $userList)) {
                $this->_error('1004');
            }

            //获取
            $userInfo = Users::model()->getUserInfoByUsername($fromgw['GW']);
            $storeInfo = Users::model()->getUserInfoByUsername($togw['GW']);
            if ($userInfo && $storeInfo) {
                $token = RongCloud::model()->rongCloudToken($userInfo['username'], $userInfo['nickname'], $userInfo['headPortrait']);

                $result = [
                    "fromgw" => [
                        "userId" => $userInfo['username'], //以GW号为准
                        "userNickname" => $userInfo['nickname'],
                        "userHead" => $userInfo['headPortrait']
                    ],
                    "rongToken" => $token,
                    "togw" => [
                        "userId" => $storeInfo['username'],
                        "userNickname" => $storeInfo['nickname'],
                        "userHead" => $storeInfo['headPortrait']
                    ]
                ];
                $this->_success($result);
            } else {
                throw  new Exception('用户信息获取失败或不存在此用户！');
            }

            $this->_error('0002', '获取TOKEN失败！');
        } catch (Exception $e) {
            $this->_error('0000', $e->getMessage());
        }
    }

}


