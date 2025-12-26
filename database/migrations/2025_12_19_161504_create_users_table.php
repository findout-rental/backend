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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number', 20)->unique();
            $table->string('password');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('personal_photo', 255);
            $table->date('date_of_birth');
            $table->string('id_photo', 255);
            $table->enum('role', ['tenant', 'owner', 'admin'])->default('tenant');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('language_preference', ['ar', 'en'])->default('en');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->string('fcm_token')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('role');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
