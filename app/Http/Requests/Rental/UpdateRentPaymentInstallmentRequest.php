<?php

namespace App\Http\Requests\Rental;

use App\Models\RentPaymentInstallment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentPaymentInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var RentPaymentInstallment|null $row */
        $row = $this->route('payment');
        if (! $row instanceof RentPaymentInstallment) {
            return;
        }

        $due = (float) $row->amount_due;
        $paid = (float) $this->input('amount_paid', 0);

        if ($paid >= $due && $this->input('status') === RentPaymentInstallment::STATUS_PENDING) {
            $this->merge(['status' => RentPaymentInstallment::STATUS_PAID]);
        }

        if ($this->input('status') === RentPaymentInstallment::STATUS_PAID && ! $this->filled('paid_date')) {
            $this->merge(['paid_date' => now()->toDateString()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                RentPaymentInstallment::STATUS_PENDING,
                RentPaymentInstallment::STATUS_PAID,
                RentPaymentInstallment::STATUS_OVERDUE,
                RentPaymentInstallment::STATUS_WAIVED,
            ])],
            'paid_date' => ['nullable', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
