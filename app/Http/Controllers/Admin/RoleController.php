<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const ORDERABLE = ['id', 'name', 'guard_name', 'permissions_count', 'created_at'];

    public function index(): View
    {
        return view('admin.roles.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Role::query()->withCount('permissions');

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $recordsTotal = Role::query()->count();
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
        $roles = $query->skip($start)->take($length)->get();

        $data = $roles->map(function (Role $role): array {
            $editUrl = route('admin.roles.edit', $role);
            $deleteUrl = route('admin.roles.destroy', $role);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($role->name).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $role->id,
                'name' => e($role->name),
                'guard_name' => e($role->guard_name),
                'permissions_count' => (string) $role->permissions_count,
                'created_at' => $role->created_at?->format('Y-m-d H:i') ?? '—',
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
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($this->permissionsForSync($permissionIds, $role->guard_name));

        return redirect()
            ->route('admin.roles.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Saved'),
                'text' => __('Role created successfully.'),
            ]);
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissionIds = $role->permissions->pluck('id')->all();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($this->permissionsForSync($permissionIds, $role->guard_name));

        return redirect()
            ->route('admin.roles.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Saved'),
                'text' => __('Role updated successfully.'),
            ]);
    }

    /**
     * @param  array<int|string>  $permissionIds
     * @return EloquentCollection<int, Permission>
     */
    protected function permissionsForSync(array $permissionIds, string $guardName): EloquentCollection
    {
        $ids = collect($permissionIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return new EloquentCollection;
        }

        return Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('id', $ids)
            ->get();
    }

    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        if ($role->name === 'admin') {
            if (request()->wantsJson()) {
                return response()->json(['message' => __('The admin role cannot be deleted.')], 422);
            }

            return redirect()
                ->route('admin.roles.index')
                ->with('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'text' => __('The admin role cannot be deleted.'),
                ]);
        }

        $role->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Role deleted.')]);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Deleted'),
                'text' => __('Role removed successfully.'),
            ]);
    }
}
