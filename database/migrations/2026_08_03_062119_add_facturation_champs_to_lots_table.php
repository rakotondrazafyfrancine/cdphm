<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute la migration : ajoute les colonnes nécessaires
     * pour la facturation du tunnel et de la chambre froide
     */
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {

            // Date/heure exacte à laquelle le lot entre en chambre froide
            // Sert à calculer le nombre de jours facturés à la sortie
            $table->dateTime('date_entree_chambre')->nullable();

            // Montant facturé pour le passage au tunnel
            // Calcul : quantité (kg) x 450 Ar x 1 jour forfaitaire
            $table->decimal('montant_tunnel', 12, 2)->nullable();

            // Montant facturé pour le séjour en chambre froide
            // Calcul : quantité (kg) x jours réels x 25 Ar
            $table->decimal('montant_chambre', 12, 2)->nullable();

            // Nombre de jours réellement passés en chambre froide
            $table->integer('jours_chambre')->nullable();

        });
    }

    /**
     * Annule la migration : supprime les colonnes ajoutées
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn(['date_entree_chambre', 'montant_tunnel', 'montant_chambre', 'jours_chambre']);
        });
    }
};
