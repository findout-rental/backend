<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('governorate', 100);
            $table->string('governorate_ar', 100)->nullable();
            $table->string('city', 100);
            $table->string('city_ar', 100)->nullable();
            $table->text('address');
            $table->text('address_ar')->nullable();
            $table->decimal('nightly_price', 10, 2);
            $table->decimal('monthly_price', 10, 2);
            $table->unsignedTinyInteger('bedrooms');
            $table->unsignedTinyInteger('bathrooms');
            $table->unsignedTinyInteger('living_rooms');
            $table->decimal('size', 8, 2);
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->json('photos')->nullable();
            $table->json('amenities')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();

            // Indexes
            $table->index('owner_id');
            $table->index('governorate');
            $table->index('governorate_ar');
            $table->index('city');
            $table->index('city_ar');
            $table->index('status');
            $table->index('nightly_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
