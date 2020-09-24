<?php
namespace App\Kernel;

use App\Log;
use Firebase\JWT\JWT;
use Hyperf\Redis\Redis;
use Hyperf\SocketIOServer\Parser\Engine;
use Hyperf\SocketIOServer\Parser\Packet;
use Swoole\Coroutine\Channel;
use Swoole\Http\Request;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Swoole\Http\Response;
use Swoole\Timer;
use Swoole\WebSocket\Frame;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use App\Model\User;

class SocketIO extends \Hyperf\SocketIOServer\SocketIO
{
    protected $pingTimeout = 2000;

    protected $pingInterval = 9000; //心跳间隔6秒

    protected $clientCallbackTimeout = 2000;

    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    /**
     * @param Response|\Swoole\WebSocket\Server $server
     */
    public function onOpen($server, Request $request): void
    {
        $decoded = JWT::decode($request->get['token'], env('JWT_SECRET'), array('HS256'));
        $uid = $decoded->uid;
        $sid = $this->sidProvider->getSid($request->fd);
        $this->redis->set('ws:user' . $sid, $uid, 3600);
        parent::onOpen($server, $request);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $sid = $this->sidProvider->getSid($fd);
        $this->redis->sRem('socket_onlines_user', $sid);

        $uid = $this->redis->get('ws:user' . $sid);

        $user = User::findFromCache($uid);

        $msg = $user->name . '离开了房间，当前还有：' . $this->redis->sCard('socket_onlines_user') . " 人在房间中...";
        $emitData = json_encode([
            'type' => 3,
            'data' => [ 'time' => date('Y-m-d H:i:s'), 'msg' => $msg]
        ]);
        $this->emit('event', $emitData);

        parent::onClose($server, $fd, $reactorId);
    }
}