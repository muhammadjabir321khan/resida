<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaseRequest extends FormRequest
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
        $uid = auth()->id();

        return [
            'property_id' => ['required', 'integer', Rule::exists('properties', 'id')->where('user_id', $uid)],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('rental_units', 'id')->where(fn ($q) => $q->where('user_id', $uid)->where('property_id', (int) $this->input('property_id'))),
            ],
            'tenant_id' => ['required', 'integer', Rule::exists('rental_tenants', 'id')->where('user_id', $uid)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'payment_frequency' => ['required', Rule::in(['monthly', 'quarterly', 'semi_annual', 'annual'])],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'active', 'expired', 'terminated'])],
            'document_path' => ['nullable', 'string', 'max:500'],
            'terms_notes' => ['nullable', 'string'],
            'regenerate_payment_schedule' => ['nullable', 'boolean'],
        ];
    }
}
