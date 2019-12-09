<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user/all', function () {
    $userService = new \App\MicroApi\Services\UserService();
    $users = $userService->getAll();
    dd($users);
});

Route::get('/user/get', function (Request $request) {
    $email = $request->get('email');
    $userService = new \App\MicroApi\Services\UserService();
    $user = $userService->getByEmail($email);
    dd($user);
});

Route::get('/user/auth', function () {
    $userService = new \App\MicroApi\Services\UserService();
    $token = $userService->auth(['email' => 'test1@xueyuanjun.com', 'password' => '123456']);
    dd(\Firebase\JWT\JWT::decode($token, 'laracomUserTokenKeySecret', ['HS256']));
});

Route::get('/user/isAuth', function () {
    $userService = new \App\MicroApi\Services\UserService();
    $result = $userService->isAuth('123456');
    dd($result);
});

Route::get('/auth/test', function (Request $request) {
    dd($request->cookie('jwt_token'));
    dd(auth()->user());
});

Route::get('/broker/test', function (Request $request) {
    $brokerService = new App\Services\Broker\BrokerService();
    $brokerService->publish('password.reset', ['test']);
});
