<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_extension}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $nickname
 * @property string $address
 * @property int $sex 0 保密 1男 2女
 * @property string $city
 * @property string $head_portrait
 */
class UserExtension extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_extension}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'sex'], 'integer'],
            [['nickname'], 'string', 'max' => 10],
            [['address'], 'string', 'max' => 100],
            [['city'], 'string', 'max' => 20],
            [['head_portrait'], 'string', 'max' => 200],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'nickname' => 'Nickname',
            'address' => 'Address',
            'sex' => '性别',
            'city' => 'City',
            'head_portrait' => '头像'
        ];
    }
}
