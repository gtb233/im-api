<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=im_db',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => 'im_',
    #'enableSchemaCache' => true,#若数据库有变动要关闭更新以缓存
    #'schemaCacheDuration' => 24*3600,
    #'schemaCache' => 'cache',
    // 添加初始化语句
//    'on afterOpen' => function($event) {
//        // $event->sender refers to the DB connection
//        $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
//    }
    
    // 从库的通用配置
//     'slaveConfig' => [
//         'username' => 'slave',
//         'password' => '',
//         'attributes' => [
//             // 使用一个更小的连接超时
//             PDO::ATTR_TIMEOUT => 10,
//         ],
//     ],
    
    // 从库的配置列表
//     'slaves' => [
//         ['dsn' => 'dsn for slave server 1'],
//         ['dsn' => 'dsn for slave server 2'],
//         ['dsn' => 'dsn for slave server 3'],
//         ['dsn' => 'dsn for slave server 4'],
//     ],
];
