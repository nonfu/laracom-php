<?php

namespace App\Shop\Customers\Requests;

use App\Rules\UniqueEmail;
use App\Shop\Base\BaseFormRequest;

class RegisterCustomerRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', new UniqueEmail],
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
