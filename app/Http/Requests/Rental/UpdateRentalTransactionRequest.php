<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalTransactionRequest extends FormRequest
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
            'property_id' => ['nullable', 'integer', Rule::exists('properties', 'id')->where('user_id', $uid)],
            'lease_id' => ['nullable', 'integer', Rule::exists('rental_leases', 'id')->where('user_id', $uid)],
            'unit_id' => ['nullable', 'integer', Rule::exists('rental_units', 'id')->where('user_id', $uid)],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'direction' => ['required', Rule::in(['income', 'expense'])],
            'category' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'reference' => ['nullable', 'string', 'max:120'],
        ];
    }
}
