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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            
            $table->string('provider', 16)->default('MIDTRANS'); // CHECK below
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('status', 20)->default('INITIATED');  // CHECK below

            // Midtrans fields
            $table->string('midtrans_txn_id', 64)->nullable()->index();
            $table->string('va_number', 40)->nullable();
            $table->string('bank', 20)->nullable();

            $table->timestampTz('paid_at')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_provider_check
            CHECK (provider IN ('MIDTRANS','BANK_TRANSFER'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check
            CHECK (status IN ('INITIATED','PENDING','SETTLEMENT','DENY','EXPIRE','CANCEL'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
