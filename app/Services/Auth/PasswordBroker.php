<?php
namespace App\Services\Auth;

use Illuminate\Auth\Passwords\PasswordBroker as BasePasswordBroker;

class PasswordBroker extends BasePasswordBroker
{
    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @return string
     */
    public function sendResetLink(array $credentials)
    {
        // 检查用户是否存在
        $user = $this->getUser($credentials);
        if (is_null($user)) {
            return static::INVALID_USER;
        }
        // 存在的话则创建对应的密码重置记录，邮件发送操作异步去做
        $this->tokens->create($user);
        return static::RESET_LINK_SENT;
    }
}
