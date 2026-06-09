<?php

namespace App\Mail;

use App\Models\RentPaymentInstallment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantRentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RentPaymentInstallment $installment,
        public string $reminderType,
        public string $tenantName,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match (true) {
            str_starts_with($this->reminderType, 'upcoming_') => __('[:app] Rent due in :days days', [
                'app' => config('app.name', 'Residia'),
                'days' => (int) str_replace('upcoming_', '', $this->reminderType),
            ]),
            $this->reminderType === 'due_today' => __('[:app] Rent is due today', ['app' => config('app.name', 'Residia')]),
            default => __('[:app] Overdue rent reminder', ['app' => config('app.name', 'Residia')]),
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $lease = $this->installment->lease;
        $propertyName = $lease?->property?->name ?? __('Your property');
        $unitLabel = $lease?->unit?->label;
        $landlordId = (int) $this->installment->user_id;
        $payUrl = app(\App\Services\LandlordStripeService::class)->isConfigured($landlordId)
            && $this->installment->isPayableOnline()
            ? route('dashboard')
            : null;

        return new Content(
            view: 'mail.tenant-rent-reminder-html',
            with: [
                'tenantName' => $this->tenantName,
                'reminderType' => $this->reminderType,
                'propertyName' => $propertyName,
                'unitLabel' => $unitLabel,
                'dueDate' => $this->installment->due_date?->toFormattedDateString(),
                'amountDue' => number_format((float) $this->installment->amount_due, 2),
                'dashboardUrl' => route('dashboard'),
                'payUrl' => $payUrl,
            ],
        );
    }
}
