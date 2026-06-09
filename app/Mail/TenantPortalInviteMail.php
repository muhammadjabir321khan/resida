<?php

namespace App\Mail;

use App\Models\RentalTenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantPortalInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RentalTenant $tenant,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[:app] You are invited to the tenant portal', ['app' => config('app.name', 'Residia')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.tenant-portal-invite-html',
            with: [
                'tenantName' => $this->tenant->full_name,
                'landlordName' => $this->tenant->landlord?->name ?? config('app.name'),
                'inviteUrl' => route('tenant.invite.show', $this->token),
            ],
        );
    }
}
