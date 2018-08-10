<?php
/**
 * 速率限制
 */

namespace common\components;
use common\models\ARUser;
use Yii;
use yii\filters\RateLimitInterface;


class RateLimiter extends \yii\filters\RateLimiter
{

    public $rateLimitPost; //post [1,1]; //1秒内只能post提交一次
    public $rateLimitGet;  //[100,60]; //60秒内最多只请get请求100次
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        //错误页面显示,图片上传，跳过速率检查
        if($action->id=='error' || $action->id=='ueditor'){
            return true;
        }
        $user = new ARUser();
        $user->rateLimitPost = $this->rateLimitPost ? $this->rateLimitPost : [5,1]; //1秒内只能post提交一次
        $user->rateLimitGet = $this->rateLimitGet ? $this->rateLimitGet : [100,60]; //60秒内最多只请get请求100次
        $this->errorMessage = Yii::t('app','您每秒请求次数过多！');
        $request = $this->request ? : Yii::$app->getRequest();
        if ($request->isAjax && $request->isPost){
            $user->rateLimitPost = $user->rateLimitGet; // 如果是post的ajax请求，速率限制为get一样
        }
        if ($user instanceof RateLimitInterface) {
            Yii::debug('Check rate limit', __METHOD__);
            $this->checkRateLimit(
                $user,
                $request,
                $this->response ? : Yii::$app->getResponse(),
                $action
            );
        } elseif ($user) {
            Yii::info('Rate limit skipped: "user" does not implement RateLimitInterface.', __METHOD__);
        } else {
            Yii::info('Rate limit skipped: user not logged in.', __METHOD__);
        }
        return true;
    }
}