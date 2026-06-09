<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country' => ['nullable', 'string', 'max:2'],
            'property_type' => ['required', 'string', 'max:64'],
            'units_count' => ['nullable', 'integer', 'min:1', 'max:255'],
            'market_value' => ['nullable', 'numeric', 'min:0'],
            'owner_display_name' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
