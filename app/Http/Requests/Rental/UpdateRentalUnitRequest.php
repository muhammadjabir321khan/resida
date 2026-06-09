<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amenities' => array_values(array_filter($this->input('amenities', []))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uid = auth()->id();

        return [
            'property_id' => ['required', 'integer', Rule::exists('properties', 'id')->where('user_id', $uid)],
            'label' => ['required', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:64'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:64'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
