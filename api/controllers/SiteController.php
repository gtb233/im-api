<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return json_encode(['欢迎使用！']);
    }

    public function actionError()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $exception = \Yii::$app->getErrorHandler()->exception;
        $baseData = substr($exception->getFile(),strlen(\Yii::$app->basePath)).'['.$exception->getLine().']';
        return [
            'status' =>$exception->statusCode,
            'message' => $exception->getMessage(),
            'data' => YII_DEBUG ? $exception->getTraceAsString() : $baseData
        ];
    }

}
