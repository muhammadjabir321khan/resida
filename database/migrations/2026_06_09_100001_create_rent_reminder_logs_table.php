<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_payment_installment_id')->constrained('rent_payment_installments')->cascadeOnDelete();
            $table->string('reminder_type', 32);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['rent_payment_installment_id', 'reminder_type'], 'rent_reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_reminder_logs');
    }
};
