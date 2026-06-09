<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_units', function (Blueprint $table): void {
            $table->string('occupancy_status', 20)->default('vacant')->after('is_active');
            $table->index(['user_id', 'occupancy_status']);
        });
    }

    public function down(): void
    {
        Schema::table('rental_units', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'occupancy_status']);
            $table->dropColumn('occupancy_status');
        });
    }
};
