<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(new Response(collect(
                ["message" => $validator->errors()->first(),"status" => false]
            ),422));
        }
        parent::failedValidation($validator);
    }
    public function rules()
    {
        return [
            "cart" => "required|array|max:4|min:1",
            "cart.*.name" => "required|string|distinct|".Rule::in(['pants','t-shirt','jacket','shoes']),
            "cart.*.qty" => "required|integer|min:1|max:999999999",
            "currency" => "sometimes|string|".Rule::in(['EGP','USD']),
        ];
    }

    public function attributes()
    {
        return [
            'qty' => "quantity",
        ];
    }

    public function messages()
    {
        return [
            "currency.in" => "EGP OR USD",
            "cart.*.name.in" => "name value should be one  of ['pants','t-shirt','jacket','shoes']"
        ];
    }
}
