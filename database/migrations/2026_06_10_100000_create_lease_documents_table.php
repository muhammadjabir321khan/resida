<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->constrained('rental_leases')->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 32)->default('other');
            $table->string('file_path');
            $table->string('original_filename');
            $table->boolean('is_visible_to_tenant')->default(true);
            $table->timestamps();

            $table->index(['lease_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_documents');
    }
};
