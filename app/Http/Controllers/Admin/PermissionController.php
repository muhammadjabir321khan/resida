<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private const ORDERABLE = ['id', 'name', 'guard_name', 'created_at'];

    public function index(): View
    {
        return view('admin.permissions.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Permission::query();

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $recordsTotal = Permission::query()->count();
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
        $permissions = $query->skip($start)->take($length)->get();

        $data = $permissions->map(function (Permission $permission): array {
            $editUrl = route('admin.permissions.edit', $permission);
            $deleteUrl = route('admin.permissions.destroy', $permission);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($permission->name).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $permission->id,
                'name' => e($permission->name),
                'guard_name' => e($permission->guard_name),
                'created_at' => $permission->created_at?->format('Y-m-d H:i') ?? '—',
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
        return view('admin.permissions.create');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create($request->validated());

        return redirect()
            ->route('admin.permissions.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Saved'),
                'text' => __('Permission created successfully.'),
            ]);
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update($request->validated());

        return redirect()
            ->route('admin.permissions.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Saved'),
                'text' => __('Permission updated successfully.'),
            ]);
    }

    public function destroy(Permission $permission): RedirectResponse|JsonResponse
    {
        $permission->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Permission deleted.')]);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Deleted'),
                'text' => __('Permission removed successfully.'),
            ]);
    }
}
