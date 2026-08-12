<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        DB::statement("ALTER TABLE lots MODIFY origine ENUM('frais_a_congeler', 'deja_congele') NOT NULL");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            //
        });
    }
};
