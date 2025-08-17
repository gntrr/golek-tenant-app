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
        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();

            $table->string('code', 30); // e.g. A1, VIP-A1
            $table->unsignedInteger('base_price')->default(0); // in smallest unit (e.g. IDR)
            $table->string('status', 12)->default('AVAILABLE'); // CHECK below
            $table->timestampTz('expires_at')->nullable(); // hold expiry

            $table->timestamps();

            $table->unique(['event_id', 'code']);
            $table->index(['event_id', 'status']);
            $table->index(['zone_id', 'status']);
            $table->index(['expires_at']);
        });

        // Postgres CHECK constraint for status
        DB::statement("ALTER TABLE booths ADD CONSTRAINT booths_status_check 
            CHECK (status IN ('AVAILABLE','ON_HOLD','BOOKED','DISABLED'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booths');
    }
};
