<?php
namespace App\Services\Auth;

use InvalidArgumentException;
use Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;

class PasswordBrokerManager extends BasePasswordBrokerManager
{
    /**
     * Resolve the given broker.
     *
     * @param  string  $name
     * @return PasswordBroker
     *
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("密码重置器 [{$name}] 未定义");
        }

        // The password broker uses a token repository to validate tokens and send user
        // password e-mails, as well as validating that password reset process as an
        // aggregate service of sorts providing a convenient interface for resets.
        return new PasswordBroker(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'] ?? null)
        );
    }

    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param  array  $config
     * @return \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        return new ServiceTokenRepository();
    }
}
