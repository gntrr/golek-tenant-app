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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name', 60); // VIP, FEST, FOOD, REGULER, etc.
            $table->string('color', 20)->nullable(); // Hex / RGB
            $table->decimal('price_multiplier', 5, 2)->default(1.00); // optional
            $table->timestamps();

            $table->unique(['event_id', 'name']);
            $table->index(['event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
