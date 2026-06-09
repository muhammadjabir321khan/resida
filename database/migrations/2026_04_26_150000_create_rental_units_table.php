<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('unit_type')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->decimal('bathrooms', 4, 1)->nullable();
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->json('amenities')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_units');
    }
};
