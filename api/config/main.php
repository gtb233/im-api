<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php'
);

return [
    'id' => 'app-api',
    'name' => 'api接口',
    'language'=>'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'charset'=>'utf-8',
    'defaultRoute'=>'site',//指定未配置的请求的响应 路由 规则

    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],

    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
            // ... 模块其他配置 ...
        ],
    ],

    'components' => [
        'request' => [
            //'csrfParam' => '_csrf-api',
            'enableCsrfCookie'=>false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser', #为了API接收JSON格式输入
            ]
        ],
        'user' => [
            'identityClass' => 'api\models\Users',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'im-api',
        ],
//        'response' => [
//            'class' => 'yii\web\Response',
//             'on beforeSend' => function ($event) {
//                 $response = $event->sender;
//                 if ($response->data !== null) {
//                     $response->data = [
//                         'success' => $response->isSuccessful,
//                         'data' => $response->data,
//                     ];
//                     $response->statusCode = 200;
//                 }
//             },
//        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,  #调试时堆栈3层，否则没有调用堆栈信息被包含
            'targets' => [
//                [
//                    'class' => 'yii\log\FileTarget',
//                    'logVars' => ['_GET'], #追加一些上下文信息  默认为 _SERVER 可不设置
//                    'levels' => ['error'],
//                    #'flushInterval' => 100,   // default is 1000  刷新(执行有问题，待修改)
//                    #'exportInterval' => 100,  // default is 1000  导出（日志目标累积了一定数量的过滤消息的时候才会发生）
//                    'except' => [#黑名单
//                        'yii\web\HttpException:404',
//                    ],
//                    'prefix' => function ($message) {#自定义消息前缀
//                        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
//                        $userID = $user ? $user->getId(false) : '-';
//                        return "[$userID]";
//                    }
//                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error'],#可设置 'error', 'warning', 'info', 'trace' 'profile'( Yii::beginProfile() 和 Yii::endProfile())
                    'categories' => [#白名单
                        'yii\db\*',
                        'yii\web\HttpException:*',
                    ],
                    'except' => [#黑名单
                        'yii\web\HttpException:404',
                    ],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
            #'errorView' => '', //可以配置错误处理器的 errorView 和 exceptionView 属性 使用自定义的错误显示视图。
        ],
        'urlManager' => [
            //用于表明urlManager是否启用URL美化功能，在Yii1.1中称为path格式URL，
            // Yii2.0中改称美化。
            // 默认不启用。但实际使用中，特别是产品环境，一般都会启用。
            "enablePrettyUrl" => true,
            // 是否启用严格解析，如启用严格解析，要求当前请求应至少匹配1个路由规则，
            // 否则认为是无效路由。
            // 这个选项仅在 enablePrettyUrl 启用后才有效。
            "enableStrictParsing" => false,
            // 是否在URL中显示入口脚本。是对美化功能的进一步补充。
            "showScriptName" => false,
            // 指定续接在URL后面的一个后缀，如 .html 之类的。仅在 enablePrettyUrl 启用时有效。
            "suffix" => "",
            "rules" => [
                "<controller:\w+>/<id:\d+>"=>"<controller>/view",
                "<controller:\w+>/<action:\w+>"=>"<controller>/<action>",
                "<module:\w+>/<controller:\w+>/<action:\w+>"=>"<module>/<controller>/<action>",
            ],
        ],
    ],
    'params' => $params,
];
