<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('rental_tenants')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('security_deposit', 12, 2)->nullable();
            $table->string('status')->default('draft');
            $table->string('document_path')->nullable();
            $table->text('terms_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['property_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_leases');
    }
};
