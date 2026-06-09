<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalUnitRequest;
use App\Http\Requests\Rental\UpdateRentalUnitRequest;
use App\Models\Property;
use App\Models\RentalUnit;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalUnitController extends Controller
{
    private const ORDERABLE = ['id', 'label', 'monthly_rent', 'bedrooms', 'occupancy_status', 'is_active', 'created_at'];

    private const AMENITY_OPTIONS = ['parking', 'gym', 'pool', 'balcony', 'elevator', 'security', 'storage', 'ac'];

    public function __construct(
        private SubscriptionPlanService $planService,
    ) {}

    public function index(): View
    {
        $planUsage = $this->planService->usageSummary(auth()->user());

        return view('rental.units.index', compact('planUsage'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = RentalUnit::query()->with('property');

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%")
                    ->orWhereHas('property', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsTotal = RentalUnit::query()->count();
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

        $data = $rows->map(function (RentalUnit $unit): array {
            $occ = match ($unit->occupancy_status ?? RentalUnit::OCCUPANCY_VACANT) {
                RentalUnit::OCCUPANCY_OCCUPIED => '<span class="badge badge-light-primary">'.e(__('Occupied')).'</span>',
                default => '<span class="badge badge-light-secondary">'.e(__('Vacant')).'</span>',
            };
            $active = $unit->is_active
                ? '<span class="badge badge-light-success">'.e(__('Active')).'</span>'
                : '<span class="badge badge-light-secondary">'.e(__('Inactive')).'</span>';
            $editUrl = route('rental.units.edit', $unit);
            $deleteUrl = route('rental.units.destroy', $unit);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($unit->label).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $unit->id,
                'property' => e($unit->property?->name ?? '—'),
                'label' => e($unit->label),
                'bedrooms' => $unit->bedrooms !== null ? (string) $unit->bedrooms : '—',
                'monthly_rent' => e(number_format((float) $unit->monthly_rent, 2)),
                'occupancy_status' => $occ,
                'is_active' => $active,
                'created_at' => $unit->created_at?->format('Y-m-d H:i') ?? '—',
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

    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        $planUsage = $this->planService->usageSummary($user);

        if ($planUsage['at_limit']) {
            return redirect()->route('rental.units.index')->with('swal', [
                'icon' => 'warning',
                'title' => __('Unit limit reached'),
                'text' => __('Your :plan plan allows up to :limit units. Upgrade to add more.', [
                    'plan' => $planUsage['plan_name'] ?? __('current'),
                    'limit' => $planUsage['unit_limit'],
                ]),
            ]);
        }

        $properties = Property::orderBy('name')->get();
        $amenityOptions = self::AMENITY_OPTIONS;

        return view('rental.units.create', compact('properties', 'amenityOptions', 'planUsage'));
    }

    public function store(StoreRentalUnitRequest $request): RedirectResponse
    {
        if (! $this->planService->canAddUnit($request->user())) {
            return redirect()->route('billing.plans')->with('subscription_required', __('You have reached your unit limit. Upgrade your plan to add more units.'));
        }

        RentalUnit::create($request->validated());

        return redirect()->route('rental.units.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Unit created.'),
        ]);
    }

    public function edit(RentalUnit $unit): View
    {
        $properties = Property::orderBy('name')->get();
        $amenityOptions = self::AMENITY_OPTIONS;

        return view('rental.units.edit', compact('unit', 'properties', 'amenityOptions'));
    }

    public function update(UpdateRentalUnitRequest $request, RentalUnit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('rental.units.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Unit updated.'),
        ]);
    }

    public function destroy(RentalUnit $unit): JsonResponse|RedirectResponse
    {
        $unit->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.units.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Unit removed.'),
        ]);
    }
}
