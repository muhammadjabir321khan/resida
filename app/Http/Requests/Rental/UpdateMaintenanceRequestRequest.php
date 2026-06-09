<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('completed_at') === '' || $this->input('completed_at') === null) {
            $this->merge(['completed_at' => null]);
        } elseif (strlen((string) $this->input('completed_at')) === 16) {
            $this->merge(['completed_at' => $this->input('completed_at').':00']);
        }
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
            'rental_tenant_id' => ['nullable', 'integer', Rule::exists('rental_tenants', 'id')->where('user_id', $uid)],
            'category' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'technician_name' => ['nullable', 'string', 'max:255'],
            'reported_on' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
