<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('market_value', 14, 2)->nullable()->after('units_count');
            $table->string('owner_display_name')->nullable()->after('market_value');
            $table->string('photo_path')->nullable()->after('owner_display_name');
        });

        Schema::table('rental_tenants', function (Blueprint $table) {
            $table->string('national_id')->nullable()->after('phone');
            $table->string('nationality', 120)->nullable()->after('national_id');
            $table->date('registered_on')->nullable()->after('nationality');
        });

        Schema::table('rental_leases', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('property_id')->constrained('rental_units')->nullOnDelete();
            $table->string('payment_frequency', 32)->default('monthly')->after('monthly_rent');
        });

        Schema::table('rental_maintenance_requests', function (Blueprint $table) {
            $table->string('category')->nullable()->after('property_id');
            $table->foreignId('unit_id')->nullable()->after('category')->constrained('rental_units')->nullOnDelete();
            $table->foreignId('rental_tenant_id')->nullable()->after('unit_id')->constrained('rental_tenants')->nullOnDelete();
            $table->decimal('estimated_cost', 12, 2)->nullable()->after('rental_tenant_id');
            $table->decimal('actual_cost', 12, 2)->nullable()->after('estimated_cost');
            $table->string('technician_name')->nullable()->after('actual_cost');
        });

        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->string('vendor_name')->nullable()->after('lease_id');
            $table->foreignId('unit_id')->nullable()->after('vendor_name')->constrained('rental_units')->nullOnDelete();
        });

        Schema::create('rent_payment_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->constrained('rental_leases')->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('status', 32)->default('pending');
            $table->date('paid_date')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('payment_method', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'due_date']);
            $table->index(['lease_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_payment_installments');

        Schema::table('rental_transactions', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['vendor_name', 'unit_id']);
        });

        Schema::table('rental_maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['rental_tenant_id']);
            $table->dropColumn(['category', 'unit_id', 'rental_tenant_id', 'estimated_cost', 'actual_cost', 'technician_name']);
        });

        Schema::table('rental_leases', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'payment_frequency']);
        });

        Schema::table('rental_tenants', function (Blueprint $table) {
            $table->dropColumn(['national_id', 'nationality', 'registered_on']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['market_value', 'owner_display_name', 'photo_path']);
        });
    }
};
