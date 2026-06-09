<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalTenantRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'national_id' => ['nullable', 'string', 'max:120'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'registered_on' => ['nullable', 'date'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
