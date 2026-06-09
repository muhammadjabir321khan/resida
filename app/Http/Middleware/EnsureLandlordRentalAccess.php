<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLandlordRentalAccess
{
    /**
     * Rental CRUD is for landlords (and admins). Pure tenant accounts cannot access /rental/*.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('tenant') && ! $user->hasAnyRole(['admin', 'user', 'landlord'])) {
            return redirect()
                ->route('dashboard')
                ->with('status', __('Property and lease management is only available to landlord accounts.'));
        }

        return $next($request);
    }
}
