<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_stockages', function (Blueprint $table) {
            $table->boolean('paye')->default(false);
            $table->boolean('duree_indetermine')->default(false);
        });
    }


    public function down(): void
    {
        Schema::table('service_stockages', function (Blueprint $table) {
            //
        });
    }
};
