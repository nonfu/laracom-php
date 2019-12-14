<?php
namespace App\Services\Auth;

use App\MicroApi\Exceptions\RpcException;
use App\MicroApi\Items\UserItem;
use App\MicroApi\Services\UserService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;

class MicroUserProvider implements UserProvider
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * The auth user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Create a new auth user provider.
     *
     * @param  string  $model
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->userService = resolve('microUserService');
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws RpcException
     */
    public function retrieveById($identifier)
    {
        $user = $this->userService->getById($identifier);
        if ($user) {
            $model = $this->createModel();
            $model->fillAttributes($user);
        } else {
            $model = null;
        }
        return $model;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        try {
            $data = JWT::decode($token, config('services.micro.jwt_key'), [config('services.micro.jwt_algorithms')]);
        } catch (ExpiredException $exception) {
            Log::warning('JWT Token 已过期：' . $exception->getMessage());
            return null;  // Token 过期
        } catch (\Exception $exception) {
            Log::warning('调用 MicroUserProvider.retrieveByToken 解析 JWT Token 异常：' . $exception->getMessage());
            return null;
        }
        if ($data->exp <= time()) {
            return null;  // Token 过期
        }
        $model->fillAttributes($data->User);
        return $model;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // TODO: Implement updateRememberToken() method.
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return UserItem|Authenticatable|null
     * @throws AuthenticationException
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||empty($credentials['email']) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return null;
        }

        try {
            $user = $this->userService->getByEmail($credentials['email']);
        } catch (RpcException $exception) {
            throw new AuthenticationException("认证失败：对应邮箱尚未注册");
        }

        $model = $this->createModel();
        $model->fillAttributes($user);
        return $model;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param Authenticatable $user
     * @param array $credentials
     * @return bool
     * @throws AuthenticationException
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        try {
            if (empty($credentials['jwt_token'])) {
                $token = $this->userService->auth($credentials);
            } else {
                $token = $this->userService->isAuth($credentials['jwt_token']);
            }
        } catch (RpcException $exception) {
            $message = empty($credentials['jwt_token']) ? '注册邮箱与密码不匹配' : '令牌失效';
            throw new AuthenticationException("认证失败：" . $message);
        }
        return $token;
    }

    /**
     * Create a new instance of the model.
     *
     * @return UserItem
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * Gets the name of the Eloquent user model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
