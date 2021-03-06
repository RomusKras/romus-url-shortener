<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'geoip' => [
            'class' => 'lysenkobv\GeoIP\GeoIP'
        ],
        'geoIp2' => [
            'class' => 'scorpsan\geoip\GeoIp',
    // uncomment next line if you register on sypexgeo.net and paste your key        
    //        'keySypex' => 'key-sypexgeo-net-this',
    // if need more timeout (default 5 = 5000 millisecond)
    //        'timeout' => 6,
        ],
        'urlManager' => [
            'baseUrl' => '',
            'hostInfo' => 'http://romusurlshortener.gq',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                '<hash:\w+>' => 'link/redirect',
            ],
        ],
        'queue' => [
            'class' => \yii\queue\beanstalk\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            'host' => 'localhost',
            'port' => 11300,
            'tube' => 'queue',
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
