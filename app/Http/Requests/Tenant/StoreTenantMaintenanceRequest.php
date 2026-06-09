<?php

namespace App\Http\Requests\Tenant;

use App\Models\Lease;
use App\Models\RentalTenant;
use App\Services\TenantProfileResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->tenantProfile() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $profile = $this->tenantProfile();
        if ($profile === null) {
            return [];
        }

        $propertyIds = Lease::withoutLandlordScope()
            ->where('tenant_id', $profile->id)
            ->pluck('property_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        $unitIds = Lease::withoutLandlordScope()
            ->where('tenant_id', $profile->id)
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        return [
            'property_id' => ['required', 'integer', Rule::in($propertyIds)],
            'unit_id' => ['nullable', 'integer', Rule::in($unitIds)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:120'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ];
    }

    public function tenantProfile(): ?RentalTenant
    {
        $user = $this->user();

        return $user !== null ? TenantProfileResolver::forUser($user) : null;
    }
}
