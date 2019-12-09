<?php
namespace App\Services\Broker;

use Nats\ConnectionOptions;
use Nats\EncodedConnection;
use Nats\Encoders\JSONEncoder;

class BrokerService
{
    /**
     * @var EncodedConnection
     */
    protected $client;

    public function __construct()
    {
        $encoder = new JSONEncoder();
        $options = new ConnectionOptions([
            'host' => config('services.micro.broker_host'),
            'port' => config('services.micro.broker_port'),
        ]);
        $this->client = new EncodedConnection($options, $encoder);
        $this->client->connect();
    }

    // 订阅消息
    public function subscribe($topic, \Closure $callback)
    {
        $this->client->subscribe($topic, $callback);
    }

    // 发布消息
    public function publish($topic, $message)
    {
        $this->client->publish($topic, $message);
    }

    // 同步请求
    public function request($topic, $message, \Closure $callback)
    {
        $this->client->request($topic, $message, $callback);
    }

    // 等待消息
    public function wait($number = 0)
    {
        $this->client->wait($number);
    }
}
