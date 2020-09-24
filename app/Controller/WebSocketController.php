<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnOpenInterface;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use App\Model\User;
use App\Model\Debug;
use App\Service\QueueService;


/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;

    /**
     * @Inject
     * @var QueueService
     */
    protected $service;

    private function mappingUser(Socket $socket)
    {
        $this->redis->sAdd('socket_onlines_user', $socket->getSid());
        $this->redis->expire('socket_onlines_user', 3600);
    }

    private function getUid(&$socket)
    {
        return $this->redis->get('ws:user' . $socket->getSid());
    }

    private function getRoomPersonNum()
    {
        return $this->redis->sCard('socket_onlines_user');
    }

    /**
     * @Event("event")
     * @param string $jsonStr
     */
    public function onEvent(Socket $socket, $jsonStr)
    {
//        $request = \Hyperf\WebSocketServer\Context::get(
//            \Psr\Http\Message\ServerRequestInterface::class
//        );

        $data = Json::decode($jsonStr);
        $this->mappingUser($socket);

        $type = (int)$data['type'];
        switch ($type) {
            case 1: //进入大厅页面
                $this->onJoinRoom($socket, $data);
                break;
            case 2: //进入某个车队页面
                $this->onJoinCar($socket, $data);
                break;
            case 3: //发消息，仅为web测试
                $this->onSay($socket, $data);
                break;
        }

        // 应答
        return self::jsonSuccess();
    }

    private function onSay(&$socket, &$data)
    {
        //异步测试，实际会往库中先写入beginSync和endSync，然后再异步处理
        Debug::create(['k' => 'beginSync', 'v' => 1]);
        $this->service->push([
            'uid' => $this->getUid($socket),
            'msg' => $data['data']['msg'],
            'time' => date('Y-m-d H:i:s')
        ]);
        Debug::create(['k' => 'endSync', 'v' => 1]);

        // 将当前用户加入房间
        $room = (string)$data['data']['room_id'];

        // 向房间内其他用户推送（不含当前用户）
        $user = User::findFromCache($this->getUid($socket));
        $emitData = json_encode([
            'type' => 1,
            'data' => ['time' => date('Y-m-d H:i:s'), 'msg' => $user->name . '：' .  $data['data']['msg']]
        ]);

        $socket->to($room)->emit('event', $emitData);
    }

    private function onJoinRoom(&$socket, &$data)
    {
        // 将当前用户加入房间
        $room = (string)$data['data']['room_id'];
        $socket->join($room);

        $user = User::findFromCache($this->getUid($socket));

        // 向 本人 单点推送
        $emitData = json_encode([
            'type' => 1,
            'data' => ['time' => date('Y-m-d H:i:s'), 'msg' => "{$user->name}，欢迎你进入了房间，当前房间共有" . $this->getRoomPersonNum() . '人']
        ]);
        $socket->emit('event', $emitData); //给自己推送不要加->to，直接$socket->emit

        // 向房间内其他用户推送（不含当前用户）
        $emitData = json_encode([
            'type' => 1,
            'data' => ['time' => date('Y-m-d H:i:s'), 'msg' => "{$user->name}进入了房间，当前房间共有" . $this->getRoomPersonNum() . '人']
        ]);
        $socket->to($room)->emit('event', $emitData);
    }

    private static function jsonSuccess($data=[], $httpCode=200)
    {
        return json_encode([
            'code' => (int)$httpCode,
            'msg' => '',
            'data' => $data
        ]);
    }

    private static function jsonFail($msg, $httpCode, $code=0)
    {
        return json_encode([
            'code' => $code == 0 ? $httpCode : $code,
            'msg' => $msg,
            'data' => []
        ]);
    }
}