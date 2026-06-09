<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantMaintenanceRequest;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Services\TenantProfileResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantMaintenanceController extends Controller
{
    private function profile(): ?RentalTenant
    {
        $user = auth()->user();

        return $user !== null ? TenantProfileResolver::forUser($user) : null;
    }

    public function index(): View
    {
        $profile = $this->profile();
        abort_if($profile === null, 403);

        $requests = MaintenanceRequest::withoutLandlordScope()
            ->where('rental_tenant_id', $profile->id)
            ->with(['property', 'unit'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('tenant.maintenance.index', compact('requests', 'profile'));
    }

    public function create(): View
    {
        $profile = $this->profile();
        abort_if($profile === null, 403);

        $leases = Lease::withoutLandlordScope()
            ->where('tenant_id', $profile->id)
            ->with(['property', 'unit'])
            ->orderByDesc('start_date')
            ->get();

        return view('tenant.maintenance.create', compact('leases', 'profile'));
    }

    public function store(StoreTenantMaintenanceRequest $request): RedirectResponse
    {
        $profile = $request->tenantProfile();
        abort_if($profile === null, 403);

        $data = $request->validated();
        $property = Property::withoutLandlordScope()->findOrFail((int) $data['property_id']);

        if (! empty($data['unit_id'])) {
            $unitOk = Lease::withoutLandlordScope()
                ->where('tenant_id', $profile->id)
                ->where('property_id', $property->id)
                ->where('unit_id', (int) $data['unit_id'])
                ->exists();
            if (! $unitOk) {
                return redirect()->back()
                    ->withErrors(['unit_id' => __('This unit does not match your lease for the selected property.')])
                    ->withInput();
            }
        }

        MaintenanceRequest::withoutLandlordScope()->create([
            'user_id' => (int) $property->user_id,
            'property_id' => $property->id,
            'unit_id' => $data['unit_id'] ?? null,
            'rental_tenant_id' => $profile->id,
            'category' => $data['category'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'open',
            'priority' => $data['priority'],
            'reported_on' => now()->toDateString(),
        ]);

        return redirect()->route('tenant.maintenance.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Submitted'),
            'text' => __('Your landlord has been notified of this maintenance request.'),
        ]);
    }
}
