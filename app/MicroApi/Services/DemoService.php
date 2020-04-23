<?php
namespace App\MicroApi\Services;

use App\MicroApi\Exceptions\RpcException;
use App\MicroApi\Facades\HttpClient;
use Illuminate\Support\Facades\Log;

class DemoService
{
    use DataHandler;

    protected $servicePrefix = '/demo';

    public function sayHello($name)
    {
        // 发起客户端 API 请求
        $path = $this->servicePrefix . '/hello/' . $name;
        try {
            $response = HttpClient::get($path);
        } catch (\Exception $exception) {
            Log::error("MicroApi.DemoService.SayHello Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        // 返回结果
        $result = $this->decode($response->getBody()->getContents());
        return isset($result->text) ? $result->text : null;
    }
}
