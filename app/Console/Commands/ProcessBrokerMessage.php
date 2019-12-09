<?php

namespace App\Console\Commands;

use App\MicroApi\Items\UserItem;
use App\MicroApi\Services\UserService;
use App\Services\Broker\BrokerService;
use Illuminate\Console\Command;

class ProcessBrokerMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:broker-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe and process message from micro broker';

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->userService = resolve("microUserService");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $broker = new BrokerService();
        $broker->subscribe('password.reset', function ($message) {
            // 解析消息数据
            $message = $message->getBody();
            $passwordReset = json_decode(base64_decode($message['Body']));
            $email = $passwordReset->email;
            $token = $passwordReset->token;
            // 发送重置邮件
            $user = $this->userService->getByEmail($email);
            if ($user) {
                $model = new UserItem();
                $model->fillAttributes($user);
                $model->sendPasswordResetNotification($token);
                $this->info('密码重置邮件已发送[email:' . $email . ']');
            } else {
                $this->error('指定用户不存在[email:' . $email . ']');
            }
        });
        $broker->wait();
    }
}
