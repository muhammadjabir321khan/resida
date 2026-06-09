<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartSubscriptionCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, \Illuminate\Validation\Rules\In|string>>
     */
    public function rules(): array
    {
        return [
            'plan' => [
                'required',
                'string',
                Rule::in(array_keys(config('subscription.plans', []))),
            ],
        ];
    }
}
