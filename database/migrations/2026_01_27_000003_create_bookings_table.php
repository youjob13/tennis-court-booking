<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->timestampTz('start_datetime');
            $table->integer('duration_hours');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['locked', 'confirmed', 'cancelled']);
            $table->string('payment_reference')->nullable();
            $table->timestamp('lock_expires_at')->nullable();
            $table->timestamp('unlocked_after')->nullable();
            $table->timestamps();

            $table->unique(['court_id', 'start_datetime']);
            $table->index('status');
            $table->index('lock_expires_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
