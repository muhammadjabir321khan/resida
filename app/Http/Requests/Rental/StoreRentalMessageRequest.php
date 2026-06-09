<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
