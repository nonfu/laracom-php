<?php
namespace App\Http\Controllers\Front;

use App\Services\Customer\UserService;
use App\Http\Controllers\Controller;
use App\Shop\Orders\Order;
use App\Shop\Orders\Transformers\OrderTransformable;

class AccountsController extends Controller
{
    use OrderTransformable;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * AccountsController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        // 用户信息
        $user = auth()->user();

        // 分页订单信息
        $orders = $this->userService->getPaginatedOrdersByUserId($user->id);
        $orders->transform(function (Order $order) {
            return $this->transformOrder($order);
        });

        // 地址信息
        $addresses = $this->userService->getAddressesByUserId($user->id);

        return view('front.accounts', [
            'customer' => $user,
            'orders' => $orders,
            'addresses' => $addresses
        ]);
    }
}
