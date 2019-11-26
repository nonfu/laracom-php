<?php

namespace App\Shop\Customers\Requests;

use App\Rules\UniqueEmail;
use App\Shop\Base\BaseFormRequest;

class CreateCustomerRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', new UniqueEmail],
            'password' => ['required', 'min:8']
        ];
    }
}
