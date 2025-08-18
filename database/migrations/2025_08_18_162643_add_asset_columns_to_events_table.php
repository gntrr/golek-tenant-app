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
        Schema::table('events', function (Blueprint $table) {
            // Add public s3 assets url
            $table->string('flyer_path', 400)->nullable()->after('location'); // pamflet/poster
            $table->string('venue_map_path', 400)->nullable()->after('flyer_path'); // denah booth
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
            $table->dropColumn(['flyer_path', 'venue_map_path']);
        });
    }
};
