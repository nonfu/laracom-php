<?php
namespace App\MicroApi\Services;

use App\MicroApi\Exceptions\RpcException;
use App\MicroApi\Facades\HttpClient;
use App\MicroApi\Items\TokenItem;
use App\MicroApi\Items\UserItem;
use Illuminate\Support\Facades\Log;

class UserService
{
    use DataHandler;

    protected $servicePrefix = '/user/userService';

    /**
     * @param $data
     * @return UserItem
     * @throws RpcException
     */
    public function create($data)
    {
        $path = $this->servicePrefix . '/create';
        $user = new UserItem();
        if (!empty($data['name'])) {
            $user->name = $data['name'];
        }
        if (!empty($data['email'])) {
            $user->email = $data['email'];
        }
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $options = ['json' => $user];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.create Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return $result->user;
    }

    public function getAll()
    {
        $path = $this->servicePrefix . '/getAll';
        try {
            $response = HttpClient::get($path);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.getAll Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return isset($result->users) ? $result->users : null;
    }

    public function getById($id)
    {
        $path = $this->servicePrefix . '/get';
        $user = new UserItem();
        $user->id = $id;
        $options = ['json' => $user];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.getById Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return isset($result->user) ? $result->user : null;
    }

    public function getByEmail($email)
    {
        $path = $this->servicePrefix . '/get';
        $user = new UserItem();
        $user->email = $email;
        $options = ['json' => $user];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.getByEmail Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return isset($result->user) ? $result->user : null;
    }

    public function auth($credentials)
    {
        $path = $this->servicePrefix . '/auth';
        $user = new UserItem();
        if (!empty($credentials['email'])) {
            $user->email = $credentials['email'];
        }
        if (!empty($credentials['password'])) {
            $user->password = $credentials['password'];
        }
        $options = ['json' => $user];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.auth Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return $result->token;
    }

    public function isAuth($token)
    {
        $path = $this->servicePrefix . '/validateToken';
        $item = new TokenItem();
        $item->token = $token;
        $options = ['json' => $item];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.auth Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return  $result->valid;
    }
}
