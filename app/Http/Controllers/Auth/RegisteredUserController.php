<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantInviteService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function __construct(
        private TenantInviteService $inviteService,
    ) {}

    /**
     * Roles guests may pick at signup (excludes admin to avoid privilege escalation).
     *
     * @return Collection<int, Role>
     */
    protected function assignableRegistrationRoles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'admin')
            ->orderBy('name')
            ->get();
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $inviteToken = session('tenant_invite_token');
        $inviteTenant = is_string($inviteToken) && $inviteToken !== ''
            ? $this->inviteService->findPendingInvite($inviteToken)
            : null;

        return view('auth.register', [
            'roles' => $this->assignableRegistrationRoles(),
            'inviteTenant' => $inviteTenant,
            'inviteToken' => $inviteToken,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $assignable = $this->assignableRegistrationRoles();

        $roleRules = $assignable->isEmpty()
            ? ['nullable', 'string']
            : [
                'required',
                'string',
                Rule::exists('roles', 'name')->where(function ($query): void {
                    $query->where('guard_name', 'web')->where('name', '!=', 'admin');
                }),
            ];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => $roleRules,
            'invite' => ['nullable', 'string', 'max:64'],
        ]);

        $inviteToken = $request->input('invite') ?: session('tenant_invite_token');
        $inviteTenant = is_string($inviteToken) && $inviteToken !== ''
            ? $this->inviteService->findPendingInvite($inviteToken)
            : null;

        if ($inviteTenant !== null && strcasecmp((string) $request->email, (string) $inviteTenant->email) !== 0) {
            throw ValidationException::withMessages([
                'email' => __('Use :email to accept this tenant portal invite.', ['email' => $inviteTenant->email]),
            ]);
        }

        $role = $request->filled('role') ? (string) $request->input('role') : null;
        if ($inviteTenant !== null) {
            $role = 'tenant';
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($role !== null) {
            $user->assignRole($role);
        }

        event(new Registered($user));

        Auth::login($user);

        if (is_string($inviteToken) && $inviteToken !== '') {
            $this->inviteService->acceptInvite($inviteToken, $user);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
