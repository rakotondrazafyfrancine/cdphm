<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorties_chambre', function (Blueprint $table) {
            $table->string('statut')->default('en_attente_paiement')->after('montant');
            // valeurs possibles : en_attente_paiement, paye, sortie_effectuee
        });
    }

    public function down(): void
    {
        Schema::table('sorties_chambre', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
