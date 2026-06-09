<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalTenantRequest;
use App\Http\Requests\Rental\UpdateRentalTenantRequest;
use App\Models\RentalTenant;
use App\Services\TenantInviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalTenantController extends Controller
{
    private const ORDERABLE = ['id', 'full_name', 'email', 'phone', 'created_at'];

    public function __construct(
        private TenantInviteService $inviteService,
    ) {}

    public function index(): View
    {
        return view('rental.tenants.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = RentalTenant::query();

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $recordsTotal = RentalTenant::query()->count();
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

        $data = $rows->map(function (RentalTenant $tenant): array {
            $editUrl = route('rental.tenants.edit', $tenant);
            $deleteUrl = route('rental.tenants.destroy', $tenant);
            $inviteUrl = route('rental.tenants.invite', $tenant);

            $statusBadge = match (true) {
                $tenant->isPortalLinked() => '<span class="badge badge-light-success">'.e(__('Linked')).'</span>',
                $tenant->hasPendingInvite() => '<span class="badge badge-light-warning">'.e(__('Invite pending')).'</span>',
                default => '<span class="badge badge-light-secondary">'.e(__('Not invited')).'</span>',
            };

            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>';

            if (! $tenant->isPortalLinked() && filled($tenant->email)) {
                $actions .= '<button type="button" class="btn btn-sm btn-light-info js-send-invite" data-url="'.e($inviteUrl).'" data-name="'.e($tenant->full_name).'">'.e(__('Invite')).'</button>';
            }

            $actions .= '<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($tenant->full_name).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $tenant->id,
                'full_name' => e($tenant->full_name),
                'email' => e($tenant->email ?? '—'),
                'phone' => e($tenant->phone ?? '—'),
                'portal_status' => $statusBadge,
                'created_at' => $tenant->created_at?->format('Y-m-d H:i') ?? '—',
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
        return view('rental.tenants.create');
    }

    public function store(StoreRentalTenantRequest $request): RedirectResponse
    {
        RentalTenant::create($request->validated());

        return redirect()->route('rental.tenants.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Tenant created.'),
        ]);
    }

    public function edit(RentalTenant $tenant): View
    {
        return view('rental.tenants.edit', compact('tenant'));
    }

    public function update(UpdateRentalTenantRequest $request, RentalTenant $tenant): RedirectResponse
    {
        $tenant->update($request->validated());

        return redirect()->route('rental.tenants.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Tenant updated.'),
        ]);
    }

    public function destroy(RentalTenant $tenant): JsonResponse|RedirectResponse
    {
        $tenant->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.tenants.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Tenant removed.'),
        ]);
    }

    public function sendInvite(RentalTenant $tenant): JsonResponse|RedirectResponse
    {
        try {
            $this->inviteService->sendInvite($tenant);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? __('Could not send invite.');

            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->withErrors($e->errors());
        }

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Portal invite sent.')]);
        }

        return redirect()->back()->with('swal', [
            'icon' => 'success',
            'title' => __('Invite sent'),
            'text' => __('The tenant will receive an email with a link to join the portal.'),
        ]);
    }
}
