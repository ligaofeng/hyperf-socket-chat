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

use Monolog\Formatter;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$appEnv = env('APP_ENV', 'dev');

if ($appEnv == 'dev' || $appEnv == 'local') {
    $formatter = [
        'class' => \Monolog\Formatter\JsonFormatter::class,
        'constructor' => [
            //'format' => "||%datetime%||%channel%||%level_name%||%message%||%context%||%extra%\n",
            'allowInlineLineBreaks' => true,
            'includeStacktraces' => true,
            'dateFormat' => 'Y-m-d H:i:s'
        ],
    ];
} else {
    $formatter = [
        'class' => \Monolog\Formatter\JsonFormatter::class,
        'constructor' => [
            'batchMode' => Formatter\JsonFormatter::BATCH_MODE_JSON,
            'appendNewline' => true
        ],
    ];
}

return [
    'default' => [

        'handlers' => [
            [
                'class' => RotatingFileHandler::class, //生成的日志文件按日期生成
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/general.log',
                    'level' => Logger::INFO,
                ],
                'formatter' => $formatter,
            ],
            [
                'class' => RotatingFileHandler::class, //生成的日志文件按日期生成
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Logger::DEBUG,
                ],
                'formatter'  => $formatter,
            ],
        ],


        /*

        'handler' => [
            //'class' => \Monolog\Handler\StreamHandler::class,
            'class' => Monolog\Handler\RotatingFileHandler::class, //生成的日志文件按日期生成
            'constructor' => [
                //'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => \Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => $formatter,
*/


    ],
];

//
//return [
//    'default' => [
//        'handler' => [
//            'class' => Monolog\Handler\StreamHandler::class,
//            'constructor' => [
//                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
//                'level' => Monolog\Logger::DEBUG,
//            ],
//        ],
//        'formatter' => [
//            'class' => Monolog\Formatter\LineFormatter::class,
//            'constructor' => [
//                'format' => null,
//                'dateFormat' => 'Y-m-d H:i:s',
//                'allowInlineLineBreaks' => true,
//            ],
//        ],
//    ],
//];
