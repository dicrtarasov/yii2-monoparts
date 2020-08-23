<?php

/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 03:05:56
 */
declare(strict_types = 1);

/** среда разработки */
defined('YII_ENV') || define('YII_ENV', 'dev');

/** режим отладки */
defined('YII_DEBUG') || define('YII_DEBUG', true);

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

/** @noinspection PhpUnhandledExceptionInspection */
new yii\console\Application([
    'id' => 'test',
    'basePath' => __DIR__,
    'components' => [
        'cache' => yii\caching\ArrayCache::class,
        'urlManager' => [
            'scriptUrl' => '/index.php',
            'hostInfo' => 'https://github.com',
            'baseUrl' => ''
        ]
    ],
    'modules' => [
        'monoparts' => [
            'class' => dicr\monoparts\MonoPartsModule::class,
            'url' => dicr\monoparts\MonoParts::TEST_URL,
            'storeId' => dicr\monoparts\MonoParts::TEST_STORE_ID,
            'secretKey' => dicr\monoparts\MonoParts::TEST_KEY
        ]
    ]
]);
