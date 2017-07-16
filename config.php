'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
'components' => [
'cache' => [
'class' => 'yii\redis\Cache',
'redis' => 'redis',
],
'urlManager' => [
'cache' => false
],
'mutex' => [
'class' => 'yii\mutex\FileMutex',
],
],

<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'tb_',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
        ],
        // 只读数据库
        'db_read' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'tb_',
            'charset' => 'utf8',

//            'class' => 'yii\db\Connection',
//            'dsn' => 'mysql:host=rdst3hwcnxrpow0v3ej46public.mysql.rds.aliyuncs.com;dbname=koudai',
//            'username' => 'kd_read',
//            'password' => 'kdrd_2015',
//            'charset' => 'utf8',
//            'tablePrefix' => 'tb_',
        ],
        // 财务数据库
        'db_financial' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai_financial',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'tb_',
            'charset' => 'utf8',
        ],
        // 社区数据库连接
        'db_bbs' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=wecenter',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'aws_',
            'charset' => 'utf8',
        ],
        // 支付平台数据库
        'db_pay' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai_pay',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'kd_',
            'charset' => 'utf8',
        ],
        // 运营活动数据库
        'db_event' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai_event',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'tb_',
            'charset' => 'utf8',
        ],

        //口袋快借数据库
        'db_kdkj' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai_asset',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'tb_',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
        ],
        //口袋快借只读数据库
        'db_kdkj_rd' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-bp1tl9o6hua518e29o.mysql.rds.aliyuncs.com;dbname=koudai_asset',
            'username' => 'kd_test',
            'password' => 'Kdlc#test@123',
            'tablePrefix' => 'tb_',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.8.101',
            // 'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
        'redis_crazy' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.8.101',
            'port' => 6379,
            'database' => 0,
        ],
        'redis_deposit' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.8.101',
            'port' => 6379,
            'database' => 2,
        ],
        'redis_wdzj' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '192.168.8.101',
            'port' => 6379,
            'database' => 1,
        ],
        'mongodb_backend' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://applog:app_log@192.168.8.101:27017/backend',
        ],
        'mongodb_frontend' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://applog:app_log@192.168.8.101:27017/frontend',
        ],
        'mongodb_log' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://userlog:user_log_kd@192.168.8.101:27017/log',
        ],
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://applog:app_log@192.168.8.101:27017/applog',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false, // 这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SmtpTransport',

                'host' => 'smtp.exmail.qq.com',
                'username' => 'service@koudailc.com',
                'password' => 'Koudailicai123',
                'port' => '465',
                'encryption' => 'ssl',
            ],
            // 'viewPath' => Yii::getAlias('@frontend/web/attachment/mail'),
            'viewPath' => Yii::getAlias('@common/mail'),
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['service@koudailc.com'=>'koudailicai']
            ],
        ],
    ],
];

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/../../environments/' . YII_ENV . '/frontend/config/params-local.php')
);

return [
    'id' => 'app-frontend',
    'name' => '口袋理财',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'class' => 'frontend\components\User',
            // 允许使用auth_key来自动登录
            'enableAutoLogin' => true,
            // 设为null避免跳转
            'loginUrl' => null,
        ],
        'session' => [
            // 使用redis做session
            'class' => 'yii\redis\Session',
            'redis' => 'redis',
            // 与后台区分开会话key，保证前后台能同时单独登录
            'name' => 'SESSIONID',
            'timeout' => 20 * 24 * 3600,
            'cookieParams' => ['lifetime' => 12 * 3600, 'httponly' => true, 'domain' => YII_ENV_PROD ? '.koudailc.com' : ''],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                // ------- 全局通用日志配置 begin -------
                // UserException太多，先不打到mongodb，后面再看，所有错误打文件日志也先保留
                [
                    'class' => 'yii\mongodb\log\MongoDbTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\base\UserException', 'yii\web\HttpException*'],
                    'logCollection' => 'kd_frontend_error',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\mongodb\log\MongoDbTarget',
                    'levels' => ['info', 'trace'],
                    'categories' => ['application'],
                    'logCollection' => 'kd_frontend_info',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\mongodb\log\MongoDbTarget',
                    'levels' => ['info'],
                    'categories' => ['koudai.asset.*'],
                    'logCollection' => 'kd_frontend_asset_info',
                    'logVars' => [],
                ],

                [
                    'class' => 'yii\mongodb\log\MongoDbTarget',
                    'levels' => ['info', 'trace'],
                    'categories' => ['application_op'],
                    'logCollection' => 'kd_op_info',
                    'logVars' => [],
                ],
                // ------- 全局通用日志配置 end -------

                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.pay.*'],
                    'logFile' => '@runtime/logs/notify.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.llpay.*'],
                    'logFile' => '@runtime/logs/llnotify.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.yeepay.*'],
                    'logFile' => '@runtime/logs/yeenotify.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.zmop.*'],
                    'logFile' => '@runtime/logs/zmopnotify.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.umpay.*'],
                    'logFile' => '@runtime/logs/umpaynotify.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.sms'],
                    'logFile' => '@runtime/logs/sms.log',
                    'logVars' => [],
                ],
                // 一些简单的日志统计
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.stat'],
                    'logFile' => '@runtime/logs/stat.log',
                    'logVars' => [],
                ],
                // 用户投资操作传递参数日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['koudai.projectInvestParams'],
                    'logFile' => '@runtime/logs/projectInvestParams.log',
                    'logVars' => [],
                ],
                // 长量回调用户接口log    fundbackinterface
                // 长量回调每日收益接口log fundProfitbackinterface
                // 长量回调净值接口log    fundnavbackinterface
                // 基金更改分红方式log    fundbonusChange
                // 基金撤单log          fundOrderCancel
                [
                    'class' => 'yii\mongodb\log\MongoDbTarget',
                    'levels' => ['trace', 'info', 'error', 'warning'],
                    'logCollection' => 'kd_fund_info',
                    'categories' => ['koudai.fund*'],
                    // 'logFile' => '@runtime/logs/fund.log',
                    'logVars' => [],
                ],

                // 实名认证日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['koudai.realname'],
                    'logFile' => '@runtime/logs/realname.log',
                ],
                // 支付系统接口日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['kd.pay.*'],
                    'logFile' => '@runtime/logs/kd-pay.log',
                    'logVars' => [],
                ],

                // 易宝提现日志记录
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.yeewithraw.*'],
                    'logFile' => '@runtime/logs/YeeWithdraw.log',
                    'logVars' => [],
                ],
                //用户操作日志， mongodb异常，日志写入文件系统
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['user.log.*'],
                    'logFile' => '@runtime/logs/user-log.log',
                    'logVars' => [],
                ],
                //流米确认回调日志koudai.lminterface
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.lminterface'],
                    'logFile' => '@runtime/logs/Lminterface.log',
                    'logVars' => [],
                ],
                //口袋分期电动车操作日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.loan.*'],
                    'logFile' => '@runtime/logs/loan-period.log',
                    'logVars' => [],
                ],
                //用户项目操作日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['frontend\controllers\UserProjectController*'],
                    'logFile' => '@runtime/logs/user-project.log',
                    'logVars' => [],
                ],

                //短信推送日志
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['koudai.wxpushmsg'],
                    'logFile' => '@runtime/logs/wxpushmsg.log',
                    'logVars' => [],
                ],
            ],
        ],
        // 下面是扩展了系统的组件
        'errorHandler' => [
            'class' => 'frontend\components\ErrorHandler',
        ],
        'request' => [
            'class' => 'frontend\components\Request',
        ],
    ],
    'params' => $params,
];


$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Wwpn7m5wzKDA2q141a6UVLKfK4lrfi-X',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '*.*.*.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
