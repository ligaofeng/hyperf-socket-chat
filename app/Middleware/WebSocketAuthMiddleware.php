<?php

declare(strict_types=1);

namespace App\Middleware;

use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WebSocketAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //通过 isAuth 方法拦截握手请求并实现权限检查
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }
        return $handler->handle($request);
    }

    protected function isAuth($request)
    {
        $params = $request->getQueryParams();
        try {
            $decoded = JWT::decode($params['token'], env('JWT_SECRET'), array('HS256'));
            if ($decoded->expire < time()) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
