<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedTinyInteger('number_of_guests')->nullable();
            $table->string('payment_method', 50);
            $table->decimal('total_rent', 10, 2);
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'modified_pending',
                'modified_approved',
                'modified_rejected',
                'completed'
            ])->default('pending');
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index('apartment_id');
            $table->index('status');
            $table->index(['check_in_date', 'check_out_date']);
        });

        // Add CHECK constraint using raw SQL
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT check_out_after_check_in CHECK (check_out_date > check_in_date)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
