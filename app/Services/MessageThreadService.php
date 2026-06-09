<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\RentalTenant;
use App\Models\User;
use App\Services\TenantProfileResolver;
use Illuminate\Support\Facades\Mail;

class MessageThreadService
{
    public function findOrCreateForLease(Lease $lease): MessageThread
    {
        return MessageThread::withoutLandlordScope()->firstOrCreate(
            ['lease_id' => $lease->id],
            [
                'user_id' => $lease->user_id,
                'rental_tenant_id' => $lease->tenant_id,
                'subject' => null,
            ],
        );
    }

    public function tenantCanAccess(MessageThread $thread, RentalTenant $profile): bool
    {
        return (int) $thread->rental_tenant_id === (int) $profile->id;
    }

    public function landlordCanAccess(MessageThread $thread, User $user): bool
    {
        return (int) $thread->user_id === (int) $user->id;
    }

    public function sendMessage(MessageThread $thread, User $sender, string $body): Message
    {
        $message = Message::query()->create([
            'message_thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
            'body' => trim($body),
        ]);

        $thread->update(['last_message_at' => $message->created_at]);

        if ((int) $thread->user_id === (int) $sender->id) {
            $thread->update(['landlord_last_read_at' => now()]);
            $this->notifyTenant($thread, $message);
        } else {
            $thread->update(['tenant_last_read_at' => now()]);
            $this->notifyLandlord($thread, $message);
        }

        return $message;
    }

    public function markReadForLandlord(MessageThread $thread): void
    {
        $thread->update(['landlord_last_read_at' => now()]);
    }

    public function markReadForTenant(MessageThread $thread): void
    {
        $thread->update(['tenant_last_read_at' => now()]);
    }

    private function notifyTenant(MessageThread $thread, Message $message): void
    {
        $profile = $thread->rentalTenant;
        if ($profile === null) {
            return;
        }

        $tenantUser = TenantProfileResolver::userForProfile($profile);
        if ($tenantUser === null || blank($tenantUser->email)) {
            return;
        }

        Mail::to($tenantUser->email)->send(new \App\Mail\NewRentalMessageMail(
            $thread,
            $message,
            $tenantUser->name ?? $tenantUser->email,
            route('tenant.messages.show', $thread),
        ));
    }

    private function notifyLandlord(MessageThread $thread, Message $message): void
    {
        $landlord = $thread->landlord;
        if ($landlord === null || blank($landlord->email)) {
            return;
        }

        Mail::to($landlord->email)->send(new \App\Mail\NewRentalMessageMail(
            $thread,
            $message,
            $landlord->name ?? $landlord->email,
            route('rental.messages.show', $thread),
        ));
    }
}
