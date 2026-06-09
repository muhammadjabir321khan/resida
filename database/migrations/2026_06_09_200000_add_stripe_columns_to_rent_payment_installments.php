<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_payment_installments', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('notes');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');

            $table->index('stripe_checkout_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('rent_payment_installments', function (Blueprint $table) {
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_payment_intent_id']);
        });
    }
};
