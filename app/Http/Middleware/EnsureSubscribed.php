<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    /**
     * Routes that must stay reachable without an active subscription.
     *
     * @var list<string>
     */
    protected const ALLOWED_ROUTE_NAMES = [
        'billing.plans',
        'billing.checkout',
        'billing.success',
        'cashier.payment',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if ($user->hasRole('admin') || $user->hasRole('tenant')) {
            return $next($request);
        }

        if ($user->hasIncompletePayment(config('subscription.type'))) {
            $payment = $user->subscription(config('subscription.type'))?->latestPayment();
            if ($payment !== null) {
                return redirect()->route('cashier.payment', [
                    $payment->id,
                    'redirect' => route('dashboard'),
                ]);
            }
        }

        if ($user->hasActiveSubscription()) {
            return $next($request);
        }

        return redirect()
            ->route('billing.plans')
            ->with('subscription_required', __('Choose a plan to use the rental workspace.'));
    }
}
