<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    //'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','profile'],
                    'logVars' => []
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                //'http://zhlxin.com/<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                //Url::to(['post/index', ['page' => 1, 'tag' => 'a', 'lang' => 'en']]
                [
                    'pattern' => 'post/<page:\d+>/<tag>',
                    'route' => 'post/index1',
                    'defaults' => ['page' => 1],
                    'host' => "http://<lang:en|fr>.example.com",
                ],
                [
                    'pattern' => '<controller>/index1/<page:\d+>',
                    'route' => '<controller>/index1',
                    'defaults' => ['controller' => 'zhl', 'page' => 1],
                ],
                [
                    'pattern' => '<page:\d+>/<tag>',
                    'route' => 'post/index',
                    'defaults' => ['page' => 1, ],
                ],
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',

            ],
        ],
    ],
    //'params' => $params,
];
