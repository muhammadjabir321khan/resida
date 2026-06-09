<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LandlordRentalDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>  $overdueLines
     * @param  list<string>  $leaseEndingLines
     * @param  list<string>  $maintenanceLines
     */
    public function __construct(
        public string $landlordName,
        public int $overdueCount,
        public int $leasesEndingCount,
        public int $openMaintenanceCount,
        public array $overdueLines = [],
        public array $leaseEndingLines = [],
        public array $maintenanceLines = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[:app] Daily rental summary', ['app' => config('app.name', 'Residia')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rental-landlord-digest-html',
            with: [
                'landlordName' => $this->landlordName,
                'overdueCount' => $this->overdueCount,
                'leasesEndingCount' => $this->leasesEndingCount,
                'openMaintenanceCount' => $this->openMaintenanceCount,
                'overdueLines' => $this->overdueLines,
                'leaseEndingLines' => $this->leaseEndingLines,
                'maintenanceLines' => $this->maintenanceLines,
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
