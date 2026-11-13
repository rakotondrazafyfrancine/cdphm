<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tunnels', function (Blueprint $table) {
            // moment où le tunnel est lancé : les 24 h de congélation comptent à partir de là, pas de l'enregistrement des lots
            $table->dateTime('demarre_le')->nullable()->after('capacite_tonnes');
        });
    }

    public function down(): void
    {
        Schema::table('tunnels', function (Blueprint $table) {
            $table->dropColumn('demarre_le');
        });
    }
};
