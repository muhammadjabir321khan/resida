<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RentalTenant;
use App\Services\TenantInviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TenantInviteController extends Controller
{
    public function __construct(
        private TenantInviteService $inviteService,
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $tenant = $this->inviteService->findPendingInvite($token);

        if ($tenant === null) {
            return redirect()->route('login')->withErrors([
                'invite' => __('This invite link is invalid or has already been used.'),
            ]);
        }

        $this->inviteService->rememberInviteInSession($token);

        if (Auth::check()) {
            try {
                $this->inviteService->acceptInvite($token, Auth::user());

                return redirect()->route('dashboard')->with('status', __('Your tenant portal account is now linked.'));
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('dashboard')->withErrors($e->errors());
            }
        }

        return view('tenant.invite.show', [
            'tenant' => $tenant,
        ]);
    }
}
