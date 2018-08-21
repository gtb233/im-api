<?php

namespace api\models;

use common\components\Helper;
use common\models\ARUser;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property string $id
 * @property string $username 用户名12字符（自动生成GW000000+）
 * @property string $phone 手机号
 * @property string $auth_key "remember me" 认证key
 * @property string $password_hash 密码HASH，（可空）
 * @property string $password_reset_token 密码找回TOKEN
 * @property string $email 邮箱地址
 * @property int $status 0 冻结 1正常注册用户 2纯第三方登录（无法使用正常登录）
 * @property string $created_at
 * @property string $updated_at
 */
class Users extends ARUser implements IdentityInterface
{
    const STATUS_DELETED = 0; //冻结用户
    const STATUS_ACTION = 1; //已注册用户
    const STATUS_OTHER = 2; //纯第三方来源登录

    private $userinfoKeyPrefix = 'yii:userinfo_';
    private $userListKey = 'yii:userList_';

    /**
     * @return Users
     */
    public static function model(): Users
    {
        $class = __CLASS__;
        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password_hash'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username'], 'string', 'max' => 12],
            [['phone'], 'string', 'max' => 11],
            [['auth_key', 'password_reset_token', 'email'], 'string', 'max' => 32],
            [['password_hash'], 'string', 'max' => 128],
            [['status'], 'default', 'value' => self::STATUS_ACTION],
            [['status'], 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTION]]
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
//                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
//                ],
                // if you're using datetime instead of UNIX timestamp:
                //'value' => new Expression('NOW()'),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'phone' => '手机号',
            'password_hash' => '密码',
            'email' => '邮箱地址',
            'status' => '状态',
            'created_at' => '添加时间',
            'updated_at' => '更新时间',
        ];
    }


    public function getUserExtension()
    {
        return $this->hasMany(ApiUserExtension::class, ['user_id' => 'id']);
    }

    /* ---------  根据关键字段查找   ---------- */

    public function getId()
    {
        $this->id = $this->getPrimaryKey();
    }

    /**
     * 根据用户ID查询
     * @param int|string $id
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => static::STATUS_ACTION]);
    }

    /**
     * 根据用户名查找用户基本信息
     *
     * @param string $username
     * @return array|bool
     */
    public static function findByUsername($username)
    {
        $result = static::find()->select('id, username')->where(['username' => $username])->asArray()->one();
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * 取得用户列表
     * @param bool $isCache
     * @return array
     */
    public function getUserList($isCache = true)
    {
        $result = Helper::cache()->get($this->userListKey);
        if (!$result || !$isCache){
            $query = new \yii\db\Query();
            $rows = $query->select(['u.username', 'uex.nickname'])
                ->from(['u' => '{{%users}}'])
                ->leftJoin(['uex' => '{{%user_extension}}'], 'u.id = uex.user_id')
                ->where(['>=', 'u.status', '1'])
                ->createCommand();

            //echo $rows->sql;
            $result = $rows->queryAll();
            if ($result){
                Helper::cache()->set($this->userListKey, json_encode($result), 600);
                return $result;
            }

            return [];
        }

        return json_decode($result, true);
    }

    /**
     * 根据用户名查找用户完整信息
     * @param string $username
     * @return array | bool
     */
    public function getUserInfoByUsername(string $username, $isCache = true)
    {
        $key = $this->userinfoKeyPrefix . $username;
        $userInfo = Helper::cache()->get($key);
        if (!$userInfo || !$isCache) {
            //关联方法
            /*$user = static::findOne(['username' => $username]);
            if ($user) {
                $userExtension = $user->getUserExtension()->asArray()->one();
                $resultData = [
                    'id' => $user->id,
                    'username' => $user->username
                ];

                $resultData = ArrayHelper::merge($userExtension, $resultData);
                return $resultData;
            }*/

            //join
            $userInfo = self::find()->select('u.id, u.username, ue.nickname, ue.sex, ue.head_portrait as headPortrait')
                ->from(['u' => '{{%users}}'])
                ->leftJoin(['ue' => '{{%user_extension}}'], 'u.id = ue.user_id')
                ->where(['username' => $username, 'status' => Users::STATUS_ACTION])
                ->asArray()
                ->one();
            if ($userInfo) {
                Helper::cache()->set($key, json_encode($userInfo), 3600);
                return $userInfo;
            }
            return false;
        } else {
            return json_decode($userInfo, true);
        }
    }

    /*-------------   密码相关操作  --------------*/

    /**
     * 为model的password_hash字段生成密码的hash值-注册使用
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 验证密码是否正确
     */
    public function validatePassword($passwd)
    {
        return yii::$app->security->validatePassword($passwd, $this->password_hash);
    }


    /*  ------------- remember me Authkey --------------------- */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 生成 "remember me" 认证key
     */
    public function generateAuthkey()
    {
        $this->auth_key = yii::$app->security->generateRandomString();
    }

    /* -----------------  第三方来源验证  ----------------------*/

    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /* -------------------- 密码重置验证 ----------------------------------*/

    /**
     * 验证重置密码 TOKEN是否有效
     * @param $token
     * @return bool|null
     */
    public function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return null;
        }

        $timestemp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = yii::$app->params['user.passwordResetTokenExpire'];
        return $timestemp + $expire >= time();
    }

    /**
     * 生成重置密码验证 TOKEN
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = yii::$app->security->generateRandomString() . '_' . time();
    }
}
