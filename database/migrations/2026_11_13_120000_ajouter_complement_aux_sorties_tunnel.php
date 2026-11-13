<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorties_tunnel', function (Blueprint $table) {
            // facture complémentaire créée après la contre-pesée : elle renvoie à la facture d'acompte du même lot
            $table->unsignedBigInteger('complement_de_id')->nullable()->after('lot_id');
            $table->index('complement_de_id');
        });
    }

    public function down(): void
    {
        Schema::table('sorties_tunnel', function (Blueprint $table) {
            $table->dropIndex(['complement_de_id']);
            $table->dropColumn('complement_de_id');
        });
    }
};
