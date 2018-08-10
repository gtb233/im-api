<?php
namespace api\models;

use common\models\ARUser;
use phpDocumentor\Reflection\Types\String_;
use Yii;
use yii\behaviors\TimestampBehavior;
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
    const STATUS_ACTION  = 1; //已注册用户
    const STATUS_OTHER = 2; //纯第三方来源登录

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
            [['username', 'auth_key', 'created_at', 'updated_at'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username'], 'string', 'max' => 12],
            [['phone'], 'string', 'max' => 11],
            [['auth_key', 'password_reset_token', 'email'], 'string', 'max' => 32],
            [['password_hash'], 'string', 'max' => 128],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_ACTION],
            [['status'], 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTION]]
        ];
    }

    public function behaviors()
    {
        return [
            [TimestampBehavior::class]
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
     * 根据用户名查找用户
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }


    /*-------------   密码相关操作  --------------*/

    /**
     * 为model的password_hash字段生成密码的hash值-注册使用
     * @param string $password
     */
    public function setPassword( $password)
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
        if (empty($token)){
            return null;
        }

        $timestemp = (int) substr($token, strrpos($token, '_') + 1);
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
