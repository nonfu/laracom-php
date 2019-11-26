<?php

namespace App\Rules;

use App\MicroApi\Services\UserService;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmail implements Rule
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Create a new rule instance.
     * @return void
     */
    public function __construct()
    {
        $this->userService = resolve('microUserService');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->userService->getByEmail($value) == null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '该邮箱已经被注册，请使用其它邮箱';
    }
}
