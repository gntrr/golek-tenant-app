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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            // Customer (public, no-auth)
            $table->string('customer_name', 120);
            $table->string('email', 160)->index();
            $table->string('phone', 30)->nullable();
            $table->string('company_name', 160)->nullable();

            $table->string('invoice_number', 40)->unique();
            $table->unsignedBigInteger('total_amount')->default(0);

            $table->string('payment_method', 16)->default('MIDTRANS'); // CHECK below
            $table->string('status', 20)->default('PENDING');          // CHECK below

            $table->timestampTz('expires_at')->nullable(); // payment/hold window
            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['email', 'created_at']);
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_method_check
            CHECK (payment_method IN ('MIDTRANS','BANK_TRANSFER'))");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check
            CHECK (status IN ('PENDING','AWAITING_PAYMENT','PAID','EXPIRED','CANCELLED'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
