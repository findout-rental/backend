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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'rent_payment', 'refund', 'cancellation_fee']);
            $table->decimal('amount', 10, 2);
            $table->foreignId('related_booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('related_booking_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
