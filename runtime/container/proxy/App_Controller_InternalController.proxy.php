<?php

declare (strict_types=1);
namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use App\Service\QueueService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use App\Logger;
/**
 * 
 */
class InternalController
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        self::__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var QueueService
     */
    protected $service;
    /**
     * 第三方通过传统模式投递消息，需要用队列执行，要用json格式投递参数Header包含Content-Type:application/json
     * @TODO: 此处应该在网关层加访问IP白名单，只允许特定服务IP访问
     */
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $data = $request->all();
            //
            if (empty($data['className'])) {
                $ret = ['code' => 'fail', 'msg' => '缺少传入className参数'];
                return $response->withStatus(400)->json($ret);
            }
            $this->service->push($data);
            //@TODO return $this->request->getServerParams(); IP白名单通过网关实现此处暂时不考虑
            return $response->json(['code' => 'ok']);
        } catch (Exception $ex) {
            Logger::get()->error("请求错误", $request);
        }
    }
}