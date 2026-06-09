<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreMaintenanceRequestRequest;
use App\Http\Requests\Rental\UpdateMaintenanceRequestRequest;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    private const ORDERABLE = ['id', 'title', 'status', 'priority', 'reported_on', 'created_at'];

    public function index(): View
    {
        return view('rental.maintenance-requests.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = MaintenanceRequest::query()->with(['property', 'unit']);

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('property', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsTotal = MaintenanceRequest::query()->count();
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

        $data = $rows->map(function (MaintenanceRequest $row): array {
            $editUrl = route('rental.maintenance-requests.edit', $row);
            $deleteUrl = route('rental.maintenance-requests.destroy', $row);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($row->title).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $row->id,
                'property' => e($row->property?->name ?? '—'),
                'unit' => e($row->unit?->label ?? '—'),
                'title' => e($row->title),
                'status' => e($row->status),
                'priority' => e($row->priority),
                'reported_on' => $row->reported_on?->format('Y-m-d') ?? '—',
                'created_at' => $row->created_at?->format('Y-m-d H:i') ?? '—',
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
        $unitsForJs = RentalUnit::query()->with('property')->orderBy('property_id')->orderBy('label')->get()
            ->map(fn (RentalUnit $u): array => [
                'id' => $u->id,
                'property_id' => $u->property_id,
                'label' => ($u->property?->name ?? '').' — '.$u->label,
            ]);

        return view('rental.maintenance-requests.create', compact('properties', 'tenants', 'unitsForJs'));
    }

    public function store(StoreMaintenanceRequestRequest $request): RedirectResponse
    {
        MaintenanceRequest::create($request->validated());

        return redirect()->route('rental.maintenance-requests.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Maintenance request created.'),
        ]);
    }

    public function edit(MaintenanceRequest $maintenanceRequest): View
    {
        $properties = Property::orderBy('name')->get();
        $tenants = RentalTenant::orderBy('full_name')->get();
        $unitsForJs = RentalUnit::query()->with('property')->orderBy('property_id')->orderBy('label')->get()
            ->map(fn (RentalUnit $u): array => [
                'id' => $u->id,
                'property_id' => $u->property_id,
                'label' => ($u->property?->name ?? '').' — '.$u->label,
            ]);
        $maintenance = $maintenanceRequest;

        return view('rental.maintenance-requests.edit', compact('maintenance', 'properties', 'tenants', 'unitsForJs'));
    }

    public function update(UpdateMaintenanceRequestRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $maintenanceRequest->update($request->validated());

        return redirect()->route('rental.maintenance-requests.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Maintenance request updated.'),
        ]);
    }

    public function destroy(MaintenanceRequest $maintenanceRequest): JsonResponse|RedirectResponse
    {
        $maintenanceRequest->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.maintenance-requests.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Request removed.'),
        ]);
    }
}
