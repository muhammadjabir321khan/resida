<?php

namespace App\Console\Commands;

use App\Mail\TenantRentReminderMail;
use App\Models\RentPaymentInstallment;
use App\Models\RentReminderLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRentRemindersCommand extends Command
{
    protected $signature = 'rental:send-rent-reminders';

    protected $description = 'Email tenants upcoming, due-today, and overdue rent reminders.';

    public function handle(): int
    {
        $upcomingDays = config('rental.rent_reminders.upcoming_days', [3]);
        $overdueDays = config('rental.rent_reminders.overdue_days', [1, 3, 7]);
        $today = now()->startOfDay();
        $sent = 0;

        foreach ($upcomingDays as $days) {
            $days = (int) $days;
            if ($days < 1) {
                continue;
            }

            $targetDate = $today->copy()->addDays($days)->toDateString();
            $type = RentReminderLog::upcomingType($days);

            $sent += $this->sendForDueDate($targetDate, $type);
        }

        $sent += $this->sendForDueDate($today->toDateString(), RentReminderLog::TYPE_DUE_TODAY);

        foreach ($overdueDays as $days) {
            $days = (int) $days;
            if ($days < 1) {
                continue;
            }

            $targetDate = $today->copy()->subDays($days)->toDateString();
            $type = RentReminderLog::overdueType($days);

            $sent += $this->sendForDueDate($targetDate, $type, overdue: true);
        }

        $this->info("Rent reminders sent: {$sent}");

        return self::SUCCESS;
    }

    private function sendForDueDate(string $dueDate, string $reminderType, bool $overdue = false): int
    {
        $sent = 0;

        RentPaymentInstallment::query()
            ->whereDate('due_date', $dueDate)
            ->whereIn('status', [
                RentPaymentInstallment::STATUS_PENDING,
                RentPaymentInstallment::STATUS_OVERDUE,
            ])
            ->whereDoesntHave('reminderLogs', fn ($q) => $q->where('reminder_type', $reminderType))
            ->with(['lease.tenant', 'lease.property', 'lease.unit'])
            ->chunkById(100, function ($installments) use (&$sent, $reminderType, $overdue): void {
                foreach ($installments as $installment) {
                    if ($overdue && $installment->status === RentPaymentInstallment::STATUS_PAID) {
                        continue;
                    }

                    $tenant = $installment->lease?->tenant;
                    $email = $tenant?->email;

                    if ($email === null || trim($email) === '') {
                        continue;
                    }

                    Mail::to($email)->send(new TenantRentReminderMail(
                        $installment,
                        $reminderType,
                        $tenant->full_name,
                    ));

                    RentReminderLog::query()->create([
                        'rent_payment_installment_id' => $installment->id,
                        'reminder_type' => $reminderType,
                        'sent_at' => now(),
                    ]);

                    $sent++;
                }
            });

        return $sent;
    }
}
