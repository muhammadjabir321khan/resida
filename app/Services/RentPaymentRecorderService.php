<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\RentPaymentInstallment;
use App\Models\RentalTenant;
use App\Models\RentalTransaction;
use Illuminate\Support\Facades\DB;

class RentPaymentRecorderService
{
    public function tenantCanPay(RentPaymentInstallment $installment, RentalTenant $profile): bool
    {
        if (! $installment->isPayableOnline()) {
            return false;
        }

        return Lease::withoutLandlordScope()
            ->where('id', $installment->lease_id)
            ->where('tenant_id', $profile->id)
            ->exists();
    }

    public function markPaidFromStripe(
        RentPaymentInstallment $installment,
        float $amountPaid,
        string $checkoutSessionId,
        ?string $paymentIntentId = null,
    ): RentPaymentInstallment {
        return DB::transaction(function () use ($installment, $amountPaid, $checkoutSessionId, $paymentIntentId): RentPaymentInstallment {
            $installment = RentPaymentInstallment::withoutLandlordScope()
                ->lockForUpdate()
                ->findOrFail($installment->id);

            if ($installment->status === RentPaymentInstallment::STATUS_PAID) {
                return $installment;
            }

            $installment->update([
                'amount_paid' => $amountPaid,
                'status' => RentPaymentInstallment::STATUS_PAID,
                'paid_date' => now()->toDateString(),
                'payment_method' => 'stripe',
                'receipt_number' => $paymentIntentId ?? $checkoutSessionId,
                'stripe_checkout_session_id' => $checkoutSessionId,
                'stripe_payment_intent_id' => $paymentIntentId,
            ]);

            $this->recordIncomeTransaction($installment->fresh());

            return $installment;
        });
    }

    private function recordIncomeTransaction(RentPaymentInstallment $installment): void
    {
        $lease = Lease::withoutLandlordScope()
            ->with(['property', 'tenant'])
            ->find($installment->lease_id);

        if ($lease === null) {
            return;
        }

        $reference = 'stripe:'.$installment->stripe_checkout_session_id;

        $exists = RentalTransaction::withoutLandlordScope()
            ->where('user_id', $installment->user_id)
            ->where('reference', $reference)
            ->exists();

        if ($exists) {
            return;
        }

        RentalTransaction::withoutLandlordScope()->create([
            'user_id' => $installment->user_id,
            'property_id' => $lease->property_id,
            'lease_id' => $lease->id,
            'unit_id' => $lease->unit_id,
            'direction' => RentalTransaction::DIRECTION_INCOME,
            'category' => 'rent',
            'amount' => (float) $installment->amount_paid,
            'transaction_date' => $installment->paid_date ?? now()->toDateString(),
            'description' => __('Online rent payment'),
            'reference' => $reference,
            'vendor_name' => $lease->tenant?->full_name,
        ]);
    }
}
