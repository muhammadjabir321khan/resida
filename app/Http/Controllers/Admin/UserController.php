<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ORDERABLE = ['id', 'name', 'email', 'created_at'];

    public function index(): View
    {
        return view('admin.users.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = User::query()->with('roles');

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $recordsTotal = User::query()->count();
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
        $users = $query->skip($start)->take($length)->get();

        $data = $users->map(function (User $user): array {
            $rolesHtml = $user->roles->map(fn ($r) => '<span class="badge badge-light-primary me-1">'.e($r->name).'</span>')->implode('');

            $editUrl = route('admin.users.edit', $user);
            $deleteUrl = route('admin.users.destroy', $user);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-user" data-url="'.e($deleteUrl).'" data-name="'.e($user->name).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $user->id,
                'name' => e($user->name),
                'email' => e($user->email),
                'roles' => $rolesHtml ?: '<span class="text-muted">—</span>',
                'created_at' => $user->created_at?->format('Y-m-d H:i') ?? '—',
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
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $user = User::create($data);
        $user->syncRoles($roles);

        return redirect()
            ->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Saved'),
                'text' => __('User created successfully.'),
            ]);
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($roles);

        return redirect()
            ->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Saved'),
                'text' => __('User updated successfully.'),
            ]);
    }

    public function destroy(User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === auth()->id()) {
            if (request()->wantsJson()) {
                return response()->json(['message' => __('You cannot delete your own account.')], 422);
            }

            return redirect()
                ->route('admin.users.index')
                ->with('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'text' => __('You cannot delete your own account.'),
                ]);
        }

        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('User deleted.')]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Deleted'),
                'text' => __('User removed successfully.'),
            ]);
    }
}
