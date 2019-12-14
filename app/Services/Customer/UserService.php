<?php
namespace App\Services\Customer;

use App\MicroApi\Items\UserItem;
use App\Shop\Addresses\Address;
use App\Shop\Orders\Order;
use Illuminate\Contracts\Auth\UserProvider;

class UserService
{
    /**
     * @var UserProvider
     */
    protected $provider;

    public function __construct()
    {
        $this->provider = app('auth')->createUserProvider('micro_user');
    }

    public function getById(int $id): UserItem
    {
        return $this->provider->retrieveById($id);
    }

    public function getByEmail(string $email): UserItem
    {
        return $this->provider->retrieveByCredentials(['email' => $email]);
    }

    public function getPaginatedOrdersByUserId($uid, $perPage = 15, $columns = ['*'], $orderBy = 'id')
    {
        return Order::select($columns)->where('customer_id', $uid)->orderBy($orderBy, 'desc')->paginate($perPage);
    }

    public function getAddressesByUserId($uid, $columns = ['*'], $orderBy = 'id')
    {
        return Address::select($columns)->where('customer_id', $uid)->orderBy($orderBy, 'desc')->get();
    }
}
