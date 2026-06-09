<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->constrained('rental_leases')->cascadeOnDelete();
            $table->foreignId('rental_tenant_id')->constrained('rental_tenants')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('landlord_last_read_at')->nullable();
            $table->timestamp('tenant_last_read_at')->nullable();
            $table->timestamps();

            $table->unique('lease_id');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['message_thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};
