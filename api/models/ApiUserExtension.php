<?php
/**
 * Created by PhpStorm.
 * User: gaotanbin
 * Date: 2018/8/11
 * Time: 11:17
 */

namespace api\models;


use common\models\UserExtension;

class ApiUserExtension extends UserExtension
{
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }
}