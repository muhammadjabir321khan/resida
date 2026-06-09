<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_tenants', function (Blueprint $table) {
            $table->foreignId('linked_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('invite_token', 64)->nullable()->unique()->after('notes');
            $table->timestamp('invited_at')->nullable()->after('invite_token');
            $table->timestamp('invite_accepted_at')->nullable()->after('invited_at');
        });
    }

    public function down(): void
    {
        Schema::table('rental_tenants', function (Blueprint $table) {
            $table->dropForeign(['linked_user_id']);
            $table->dropColumn(['linked_user_id', 'invite_token', 'invited_at', 'invite_accepted_at']);
        });
    }
};
