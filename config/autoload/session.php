<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\Session\Handler;
use Hyperf\Session\Handler\RedisHandler;

return [
    //'handler' => Handler\FileHandler::class,
    'handler' => RedisHandler::class,
    'options' => [
        'connection' => 'default', //default名字要与 hyperf/redis 组件的 config/autoload/redis.php 配置内的 key 命名匹配
        'path' => BASE_PATH . '/runtime/session',
        'gc_maxlifetime' => 1200,
        'session_name' => 'HYPERF_SESSION_ID',
        'domain' => null,
    ],
];
