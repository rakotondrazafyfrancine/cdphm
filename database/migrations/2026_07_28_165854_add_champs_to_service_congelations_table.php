<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_congelations', function (Blueprint $table) {
            $table->decimal('penalite', 10, 2)->default(0);
            $table->boolean('paye')->default(false);
            $table-> enum('responsabilite_penalite', ['client', 'cdphm'])->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('service_congelations', function (Blueprint $table) {

        });
    }
};
