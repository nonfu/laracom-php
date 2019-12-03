<?php
namespace App\MicroApi\Services;

use App\MicroApi\Exceptions\RpcException;
use App\MicroApi\Facades\HttpClient;
use App\MicroApi\Items\PasswordResetItem;
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

    /**
     * 创建密码重置记录
     *
     * @param $data
     * @return PasswordResetItem|null
     * @throws RpcException
     */
    public function createPasswordReset($data)
    {
        $path = $this->servicePrefix . '/createPasswordReset';
        $item = new PasswordResetItem();
        if (!empty($data['email'])) {
            $item->email = $data['email'];
        }
        if (!empty($data['token'])) {
            $item->token = $data['token'];
        }
        $options = ['json' => $item];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.createPasswordReset Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return !empty($result->passwordReset) ? $result->passwordReset : null;
    }

    /**
     * 删除密码重置记录
     *
     * @param $email
     * @return bool
     * @throws RpcException
     */
    public function deletePasswordReset($email)
    {
        $path = $this->servicePrefix . '/deletePasswordReset';
        $item = new PasswordResetItem();
        $item->email = $email;
        $options = ['json' => $item];
        try {
            HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.deletePasswordReset Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        return true;
    }

    /**
     * 验证密码重置令牌
     *
     * @param $token
     * @return bool
     * @throws RpcException
     */
    public function validatePasswordResetToken($token)
    {
        $path = $this->servicePrefix . '/validatePasswordResetToken';
        $item = new TokenItem();
        $item->token = $token;
        $options = ['json' => $item];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.validatePasswordResetToken Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return $result->valid;
    }

    /**
     * @param UserItem $item
     * @return UserItem
     * @throws RpcException
     */
    public function update(UserItem $item)
    {
        $path = $this->servicePrefix . '/update';
        $options = ['json' => $item];
        try {
            $response = HttpClient::post($path, $options);
        } catch (\Exception $exception) {
            Log::error("MicroApi.UserService.update Call Failed: " . $exception->getMessage());
            throw new RpcException("调用远程服务失败");
        }
        $result = $this->decode($response->getBody()->getContents());
        return $result->user;
    }

}
