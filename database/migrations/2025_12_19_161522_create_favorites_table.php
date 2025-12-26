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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint to prevent duplicate favorites
            $table->unique(['tenant_id', 'apartment_id']);

            // Indexes
            $table->index('tenant_id');
            $table->index('apartment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
