<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\Debug;
use Hyperf\AsyncQueue\Job;

class ExampleJob extends Job
{
    public $params;

    public function __construct($params)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->params = $params;
    }

    public function handle()
    {
        // 根据参数处理具体逻辑
        // 通过具体参数获取模型等
        echo '要处理的数据:' . PHP_EOL;
        echo "**************" . PHP_EOL;
        print_r($this->params);
        Debug::create(['k' => 'async_queue', 'v' => json_encode($this->params)]);
        echo "**************" . PHP_EOL;;
    }
}
