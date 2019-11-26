<?php
namespace App\MicroApi\Facades;

use Illuminate\Support\Facades\Facade;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @method static ResponseInterface get(string $uri, array $options = [])
 * @method static ResponseInterface post(string $uri, array $options = [])
 * @method static ResponseInterface send(RequestInterface $request, array $options = [])
 * @method static ResponseInterface request(string $method, string $uri, array $options = [])
 *
 * @see \GuzzleHttp\Client
 */
class HttpClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'HttpClient';
    }
}
