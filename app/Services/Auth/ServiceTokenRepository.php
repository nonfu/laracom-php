<?php
namespace App\Services\Auth;

use App\MicroApi\Services\UserService;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Str;

class ServiceTokenRepository implements TokenRepositoryInterface
{
    /**
     * @var UserService
     */
    protected $userService;

    public function __construct()
    {
        $this->userService = resolve('microUserService');
    }

    /**
     * Create a new token.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {
        $email = $user->getEmailForPasswordReset();

        $key = config('app.key');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $token = hash_hmac('sha256', Str::random(40), $key);

        $payload = ['email' => $email, 'token' => $token];
        $this->userService->createPasswordReset($payload);

        return $token;
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param  string $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        return $this->userService->validatePasswordResetToken($token);
    }

    /**
     * Delete a token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @return void
     */
    public function delete(CanResetPasswordContract $user)
    {
        return $this->userService->deletePasswordReset($user->email);
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        // TODO: Implement deleteExpired() method.
    }
}
