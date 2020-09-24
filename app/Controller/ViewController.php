<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\View\RenderInterface;
use Firebase\JWT\JWT;

/**
 * @AutoController
 */
class ViewController
{
    public function index(RenderInterface $render)
    {
        return $render->render('index', ['name' => 'Hyperf']);
    }

    /**
     * 前端基于socket.io.js测试socket的样例页面
     * @author ligaofeng<305429518@qq.com>
     */
    public function socket1(RenderInterface $render)
    {
        return $render->render('socket1', ['title' => 'socket测试1', 'roomId' => 1, 'token' => $this->_jwtEncode(1)]);
    }

    /**
     * 前端基于socket.io.js测试socket的样例页面
     * @author ligaofeng<305429518@qq.com>
     */
    public function socket2(RenderInterface $render)
    {
        return $render->render('socket2', ['title' => 'socket测试2', 'roomId' => 1, 'token' => $this->_jwtEncode(2)]);
    }

    /**
     * jwt加密
     * 参考：https://github.com/firebase/php-jwt
     * @param int $uid
     * @return string
     * @author ligaofeng<305429518@qq.com>
     */
    private function _jwtEncode($uid)
    {
        $nowtime = time();
        $payload = [
            "uid" => $uid,
            "expire" => $nowtime + 365 * 24 * 3600,
            "create" => $nowtime,
        ];
        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
}
