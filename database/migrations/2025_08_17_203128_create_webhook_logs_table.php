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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->index(); // 'midtrans'
            $table->string('event', 60)->nullable();
            $table->jsonb('raw_payload')->nullable();
            $table->boolean('processed')->default(false)->index();
            $table->timestampTz('processed_at')->nullable();
            $table->timestamps();

            $table->index(['provider','processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
