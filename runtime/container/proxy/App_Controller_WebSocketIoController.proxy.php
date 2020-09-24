<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
/**
 * @SocketIONamespace("/")
 */
class WebSocketIoController extends BaseNamespace
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct(\Hyperf\WebSocketServer\Sender $sender, \Hyperf\SocketIOServer\SidProvider\SidProviderInterface $sidProvider)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct(...func_get_args());
        }
        self::__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;
    /**
     * @var hall_room_prefix
     */
    protected $hall_room_prefix = 'hall:room:';
    /**
     * @var car_room_prefix
     */
    protected $car_room_prefix = 'car:room:';
    /**
     * {"hall_channel":"3724703e21d011e8104d320cd11f15c1","car_channel":"5ac11dc2d06f2186528655f6eb416123","state":1,"data":1}
     * @Event("hall-room")
     * @param string $data
     */
    public function onHallRoom(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $room = $this->hall_room_prefix . $data['hall_channel'];
        $uid = $this->getUid($socket);
        //在redis中维护一个当前房间的uid集合,并修改用缓存(user:detail:uid)
        $hallKey = sprintf('hall:online:%s', $data['hall_channel']);
        //前后台通用key
        $userKey = sprintf('user:detail:%s', $uid);
        //前后台通用key
        if ($data['state']) {
            $socket->join($room);
            if (!$this->redis->sismember($hallKey, $uid)) {
                $this->redis->sadd($hallKey, $uid);
            }
            //修改用户状态哈希值
            $this->redis->hset($userKey, 'online', 1);
        } else {
            $socket->leave($room);
            $this->redis->srem($hallKey, $uid);
            $this->redis->hset($userKey, 'online', 0);
        }
        $online = $this->redis->Smembers($hallKey);
        var_dump($online);
        //第一次进来的时候，返回大厅当前主持人和嘉宾的头像和昵称
        //读取嘉宾缓存
        $chairKey = sprintf('hall:chair:%s', $data['hall_channel']);
        $chairList = $this->redis->zrevrange($chairKey, 0, -1);
        //读取()当前大厅第一个车队人员头像和昵称
        //读取车队uid缓存
        $carKey = sprintf('car:online:%s', $data['car_channel']);
        $carList = $this->redis->Smembers($carKey);
        return Json::encode(['code' => 200, 'msg' => '', 'data' => ['chair_list' => chairList, 'car_list' => $carList]]);
    }
    /**
     * {"hall_channel":"3724703e21d011e8104d320cd11f15c1","car_channel":"5ac11dc2d06f2186528655f6eb416123","state":1}
     * @Event("hall-car")
     * @param string $data
     */
    public function onHallCar(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $room = $this->car_room_prefix . $data['car_channel'];
        $uid = $this->getUid($socket);
        //创建一个子房间
        $carKey = sprintf('car:online:%s', $data['car_channel']);
        if ($data['state']) {
            $socket->join($room);
            if (!$this->redis->sismember($carKey, $uid)) {
                $this->redis->sadd($carKey, $uid);
            }
        } else {
            $this->redis->srem($carKey, $uid);
            $socket->leave($room);
        }
        $online = $this->redis->Smembers($carKey);
        var_dump($online);
        //通知子房间其他用户
        $socket->to($room)->emit('oncar', Json::encode($data));
        //返回当前用户信息
        return Json::encode(['code' => 200, 'msg' => '', 'data' => []]);
    }
    /**
     * {"hall_channel":"3724703e21d011e8104d320cd11f15c1","state":1,"role":1}
     * @Event("hall-microphone")
     * @param string $data
     */
    public function onMicrophone(Socket $socket, $data)
    {
        //要区分主持人和嘉宾,加入一个有序集合
        $data = Json::decode($data);
        $room = $this->hall_room_prefix . $data['hall_channel'];
        $uid = $this->getUid($socket);
        $chairKey = sprintf('hall:chair:%s', $data['hall_channel']);
        if ($data['state']) {
            //自动把之前的主持人踢掉
            $old = $this->redis->zrevrange($chairKey, 0, 1);
            $this->redis->zadd($chairKey, 0, $old);
            //把当前用户设置成主持人
            $this->redis->zadd($chairKey, $data['role'], $uid);
        } else {
            $this->redis->zrem($chairKey, $uid);
        }
        $online = $this->redis->zrevrange($chairKey, 0, -1, 'WITHSCORES');
        var_dump($online);
        //通知大厅其他用户
        $socket->to($room)->emit('oncar', Json::encode($data));
        return Json::encode(['code' => 200, 'msg' => '', 'data' => []]);
    }
    private function getUid(&$socket)
    {
        return $this->redis->get('ws:user' . $socket->getSid());
    }
}