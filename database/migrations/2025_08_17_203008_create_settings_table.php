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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 80);   // e.g. payments.midtrans_enabled
            $table->string('group', 50); // 'payment','smtp','general'
            $table->text('value')->nullable();     // simpan "1"/"0" atau teks panjang
            $table->enum('type', ['bool','text','json'])->default('text');
            $table->timestamps();

            $table->primary(['group','key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
