<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreLeaseRequest;
use App\Http\Requests\Rental\UpdateLeaseRequest;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Services\LeasePaymentScheduleGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LeaseController extends Controller
{
    private const ORDERABLE = ['id', 'start_date', 'end_date', 'monthly_rent', 'status', 'created_at'];

    public function index(): View
    {
        return view('rental.leases.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Lease::query()->with(['property', 'tenant', 'unit']);

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('status', 'like', "%{$search}%")
                    ->orWhereHas('property', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('tenant', fn ($t) => $t->where('full_name', 'like', "%{$search}%"));
            });
        }

        $recordsTotal = Lease::query()->count();
        $recordsFiltered = (clone $query)->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumnName = (string) $request->input("columns.$orderColumnIndex.data", 'id');
        if (! in_array($orderColumnName, self::ORDERABLE, true)) {
            $orderColumnName = 'id';
        }
        $query->orderBy($orderColumnName, $orderDir);

        $start = max(0, (int) $request->input('start', 0));
        $length = min(max(1, (int) $request->input('length', 10)), 100);
        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (Lease $lease): array {
            $editUrl = route('rental.leases.edit', $lease);
            $deleteUrl = route('rental.leases.destroy', $lease);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e(__('Lease #').$lease->id).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $lease->id,
                'property' => e($lease->property?->name ?? '—'),
                'unit' => e($lease->unit?->label ?? '—'),
                'tenant' => e($lease->tenant?->full_name ?? '—'),
                'start_date' => $lease->start_date?->format('Y-m-d') ?? '—',
                'end_date' => $lease->end_date?->format('Y-m-d') ?? '—',
                'monthly_rent' => e(number_format((float) $lease->monthly_rent, 2)),
                'status' => e($lease->status),
                'created_at' => $lease->created_at?->format('Y-m-d H:i') ?? '—',
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create(): View
    {
        $properties = Property::orderBy('name')->get();
        $tenants = RentalTenant::orderBy('full_name')->get();
        $unitsForJs = $this->unitsForJs();

        return view('rental.leases.create', compact('properties', 'tenants', 'unitsForJs'));
    }

    public function store(StoreLeaseRequest $request): RedirectResponse
    {
        $lease = Lease::create($request->validated());
        if ($request->boolean('generate_payment_schedule') && $lease->start_date && $lease->end_date && in_array($lease->status, ['active', 'draft'], true)) {
            app(LeasePaymentScheduleGenerator::class)->sync($lease);
        }

        return redirect()->route('rental.leases.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Lease created.'),
        ]);
    }

    public function edit(Lease $lease): View
    {
        $properties = Property::orderBy('name')->get();
        $tenants = RentalTenant::orderBy('full_name')->get();
        $unitsForJs = $this->unitsForJs();
        $installmentsCount = $lease->rentPaymentInstallments()->count();
        $documents = $lease->documents()->orderByDesc('created_at')->get();

        return view('rental.leases.edit', compact('lease', 'properties', 'tenants', 'unitsForJs', 'installmentsCount', 'documents'));
    }

    public function update(UpdateLeaseRequest $request, Lease $lease): RedirectResponse
    {
        $lease->update($request->validated());
        if ($request->boolean('regenerate_payment_schedule') && $lease->start_date && $lease->end_date && in_array($lease->status, ['active', 'draft'], true)) {
            app(LeasePaymentScheduleGenerator::class)->sync($lease, replacePending: true);
        } elseif ($lease->start_date && $lease->end_date && in_array($lease->status, ['active', 'draft'], true)) {
            app(LeasePaymentScheduleGenerator::class)->sync($lease);
        }

        return redirect()->route('rental.leases.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Lease updated.'),
        ]);
    }

    public function destroy(Lease $lease): JsonResponse|RedirectResponse
    {
        $lease->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.leases.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Lease removed.'),
        ]);
    }

    /**
     * @return Collection<int, array{id: int, property_id: int, label: string}>
     */
    private function unitsForJs(): Collection
    {
        return RentalUnit::query()->with('property')->orderBy('property_id')->orderBy('label')->get()
            ->map(fn (RentalUnit $u): array => [
                'id' => $u->id,
                'property_id' => $u->property_id,
                'label' => ($u->property?->name ?? '').' — '.$u->label,
            ]);
    }
}
