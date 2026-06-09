<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RentPaymentInstallment;
use App\Services\LandlordStripeService;
use App\Services\RentPaymentRecorderService;
use App\Services\TenantProfileResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class TenantRentPaymentController extends Controller
{
    public function __construct(
        private LandlordStripeService $stripeService,
        private RentPaymentRecorderService $recorder,
    ) {}

    public function checkout(int $paymentId): RedirectResponse
    {
        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null, 403);

        $installment = RentPaymentInstallment::withoutLandlordScope()
            ->with(['lease.property', 'lease.unit'])
            ->findOrFail($paymentId);

        abort_if(! $this->recorder->tenantCanPay($installment, $profile), 403);

        $landlordId = (int) $installment->user_id;

        if (! $this->stripeService->isConfigured($landlordId)) {
            return redirect()->route('dashboard')->withErrors([
                'payment' => __('Online payments are not available yet. Contact your landlord.'),
            ]);
        }

        $amountRemaining = $installment->amountRemaining();
        $currency = $this->stripeService->currency($landlordId);
        $propertyName = $installment->lease?->property?->name ?? __('Rent payment');
        $unitLabel = $installment->lease?->unit?->label;
        $description = $unitLabel
            ? __('Rent for :property — :unit', ['property' => $propertyName, 'unit' => $unitLabel])
            : __('Rent for :property', ['property' => $propertyName]);

        try {
            $session = $this->stripeService->client($landlordId)->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => (int) round($amountRemaining * 100),
                        'product_data' => [
                            'name' => $description,
                            'description' => __('Due :date', ['date' => $installment->due_date?->toDateString() ?? '—']),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'rent_payment_installment_id' => (string) $installment->id,
                    'landlord_user_id' => (string) $landlordId,
                    'tenant_profile_id' => (string) $profile->id,
                ],
                'success_url' => route('tenant.rent.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('dashboard'),
            ]);
        } catch (ApiErrorException $e) {
            return redirect()->route('dashboard')->withErrors([
                'payment' => __('Could not start checkout. Please try again or contact your landlord.'),
            ]);
        }

        $installment->update(['stripe_checkout_session_id' => $session->id]);

        return redirect()->away($session->url);
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! is_string($sessionId) || $sessionId === '') {
            return redirect()->route('dashboard')->withErrors([
                'payment' => __('Missing checkout session.'),
            ]);
        }

        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null, 403);

        $installment = RentPaymentInstallment::withoutLandlordScope()
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        if ($installment === null) {
            return redirect()->route('dashboard')->withErrors([
                'payment' => __('Payment session not found.'),
            ]);
        }

        abort_if(! $this->recorder->tenantCanPay($installment, $profile) && $installment->status !== RentPaymentInstallment::STATUS_PAID, 403);

        if ($installment->status === RentPaymentInstallment::STATUS_PAID) {
            return redirect()->route('dashboard')->with('status', __('This rent payment was already recorded.'));
        }

        if (! $this->recorder->tenantCanPay($installment, $profile)) {
            abort(403);
        }

        $landlordId = (int) $installment->user_id;

        try {
            $session = $this->stripeService->client($landlordId)->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent'],
            ]);
        } catch (InvalidRequestException) {
            return redirect()->route('dashboard')->withErrors([
                'payment' => __('That checkout session is invalid or has expired.'),
            ]);
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('dashboard')->withErrors([
                'payment' => __('Payment was not completed.'),
            ]);
        }

        $metadataInstallmentId = (int) ($session->metadata['rent_payment_installment_id'] ?? 0);
        if ($metadataInstallmentId !== $installment->id) {
            abort(403);
        }

        $amountPaid = round($session->amount_total / 100, 2);
        $paymentIntentId = is_string($session->payment_intent)
            ? $session->payment_intent
            : ($session->payment_intent->id ?? null);

        $this->recorder->markPaidFromStripe($installment, $amountPaid, $sessionId, $paymentIntentId);

        return redirect()->route('dashboard')->with('status', __('Rent payment successful. Thank you!'));
    }
}
