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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50);
            $table->string('title', 255);
            $table->string('title_ar', 255)->nullable();
            $table->text('message');
            $table->text('message_ar')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
