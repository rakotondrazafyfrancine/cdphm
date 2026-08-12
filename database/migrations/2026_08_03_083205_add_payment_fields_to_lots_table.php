<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lots', function (Blueprint $table) {
            // Vérifier chaque colonne avant de l'ajouter
            if (!Schema::hasColumn('lots', 'paye_tunnel')) {
                $table->boolean('paye_tunnel')->default(false);
            }

            if (!Schema::hasColumn('lots', 'paye_chambre')) {
                $table->boolean('paye_chambre')->default(false);
            }

            if (!Schema::hasColumn('lots', 'montant_tunnel')) {
                $table->decimal('montant_tunnel', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('lots', 'penalite_tunnel')) {
                $table->decimal('penalite_tunnel', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('lots', 'duree_indeterminee')) {
                $table->boolean('duree_indeterminee')->default(false);
            }

            if (!Schema::hasColumn('lots', 'date_entree_chambre')) {
                $table->timestamp('date_entree_chambre')->nullable();
            }

            if (!Schema::hasColumn('lots', 'heures_tunnel')) {
                $table->integer('heures_tunnel')->nullable();
            }

            if (!Schema::hasColumn('lots', 'responsabilite_dep')) {
                $table->string('responsabilite_dep')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn([
                'paye_tunnel',
                'paye_chambre',
                'montant_tunnel',
                'penalite_tunnel',
                'duree_indeterminee',
                'date_entree_chambre',
                'heures_tunnel',
                'responsabilite_dep'
            ]);
        });
    }
};
