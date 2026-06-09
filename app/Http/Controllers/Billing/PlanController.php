<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StartSubscriptionCheckoutRequest;
use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\InvalidRequestException;
use Stripe\Subscription as StripeSubscription;

class PlanController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $subscriptionType = config('subscription.type');

        $plans = collect(config('subscription.plans', []))
            ->map(fn (array $plan, string $key): array => array_merge(
                ['key' => $key],
                $plan,
                ['configured' => filled($plan['price_id'] ?? null)],
            ))
            ->values()
            ->all();

        $subscribed = $user->subscribed($subscriptionType);
        $incomplete = $user->hasIncompletePayment($subscriptionType);
        $completePaymentUrl = null;

        if ($incomplete) {
            $payment = $user->subscription($subscriptionType)?->latestPayment();
            if ($payment !== null) {
                $completePaymentUrl = route('cashier.payment', [
                    $payment->id,
                    'redirect' => route('dashboard'),
                ]);
            }
        }

        return view('billing.plans', compact(
            'plans',
            'subscribed',
            'incomplete',
            'completePaymentUrl',
        ));
    }

    public function checkout(StartSubscriptionCheckoutRequest $request): RedirectResponse|Responsable
    {
        $user = $request->user();
        $subscriptionType = config('subscription.type');

        if ($user->subscribed($subscriptionType)) {
            return redirect()
                ->route('billing.plans')
                ->with('status', __('You already have an active subscription.'));
        }

        $planKey = $request->validated('plan');
        $priceId = config("subscription.plans.{$planKey}.price_id");

        if (! is_string($priceId) || $priceId === '') {
            return redirect()
                ->route('billing.plans')
                ->withErrors(['plan' => __('This plan is not configured. Set STRIPE_PRICE_* in your environment.')]);
        }

        try {
            return $user
                ->newSubscription($subscriptionType, $priceId)
                ->checkout([
                    'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('billing.plans'),
                ]);
        } catch (IncompletePayment $e) {
            return redirect()->route('cashier.payment', [
                $e->payment->id,
                'redirect' => route('dashboard'),
            ]);
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! is_string($sessionId) || $sessionId === '') {
            return redirect()
                ->route('billing.plans')
                ->withErrors(['session' => __('Missing checkout session. Return from Stripe Checkout, or open Plans & billing and subscribe again.')]);
        }

        /** @var User $user */
        $user = $request->user();

        try {
            $session = $user->stripe()->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription'],
            ]);
        } catch (InvalidRequestException) {
            return redirect()
                ->route('billing.plans')
                ->withErrors(['session' => __('That checkout session is invalid or has expired.')]);
        }

        if ($session->customer !== $user->stripe_id) {
            abort(403);
        }

        if ($session->mode !== 'subscription' || ! $session->subscription) {
            return redirect()
                ->route('billing.plans')
                ->withErrors(['session' => __('This checkout did not create a subscription.')]);
        }

        $stripeSubscriptionId = is_string($session->subscription)
            ? $session->subscription
            : $session->subscription->id;

        try {
            $stripeSubscription = $user->stripe()->subscriptions->retrieve($stripeSubscriptionId, [
                'expand' => ['items.data.price.product'],
            ]);
        } catch (InvalidRequestException) {
            return redirect()
                ->route('billing.plans')
                ->withErrors(['session' => __('Could not load the subscription from Stripe.')]);
        }

        $this->syncStripeSubscriptionToDatabase($user, $stripeSubscription);

        if ($user->hasIncompletePayment(config('subscription.type'))) {
            $payment = $user->subscription(config('subscription.type'))?->latestPayment();
            if ($payment !== null) {
                return redirect()->route('cashier.payment', [
                    $payment->id,
                    'redirect' => route('dashboard'),
                ]);
            }
        }

        return redirect()
            ->route('dashboard')
            ->with('status', __('Your subscription is active. Welcome back!'));
    }

    /**
     * Persist a Stripe subscription locally (mirrors Cashier's customer.subscription.created webhook).
     * Needed when webhooks are not delivered (e.g. localhost without stripe listen).
     */
    private function syncStripeSubscriptionToDatabase(User $user, StripeSubscription $data): void
    {
        $items = $data->items->data;
        if ($items === []) {
            return;
        }

        $firstItem = $items[0];
        $isSinglePrice = count($items) === 1;

        $meta = $data->metadata !== null ? $data->metadata->toArray() : [];
        $subscriptionType = $meta['type'] ?? $meta['name'] ?? config('subscription.type', 'default');

        $trialEndsAt = $data->trial_end
            ? Carbon::createFromTimestamp($data->trial_end)
            : null;

        $subscription = $user->subscriptions()->updateOrCreate(
            ['stripe_id' => $data->id],
            [
                'type' => $subscriptionType,
                'stripe_status' => $data->status,
                'stripe_price' => $isSinglePrice ? $firstItem->price->id : null,
                'quantity' => $isSinglePrice && $firstItem->quantity !== null ? $firstItem->quantity : null,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => null,
            ]
        );

        foreach ($items as $item) {
            $product = $item->price->product;
            $productId = is_string($product) ? $product : $product->id;

            $subscription->items()->updateOrCreate(
                ['stripe_id' => $item->id],
                [
                    'stripe_product' => $productId,
                    'stripe_price' => $item->price->id,
                    'quantity' => $item->quantity ?? null,
                ]
            );
        }

        if ($user->trial_ends_at !== null) {
            $user->forceFill(['trial_ends_at' => null])->save();
        }

        $user->unsetRelation('subscriptions');
    }
}
