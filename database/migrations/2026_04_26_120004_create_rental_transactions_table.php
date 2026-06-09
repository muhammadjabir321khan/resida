<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained('rental_leases')->nullOnDelete();
            $table->string('direction');
            $table->string('category')->default('other');
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_transactions');
    }
};
