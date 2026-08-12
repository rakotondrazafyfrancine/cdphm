<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Detais du comptage a la reception
            $table->integer('nb_filets')->default(0);
            $table->integer('nb_poissons')->default(0);
            $table->integer('nb_bacs')->default(0);

            // Poids a l'entree (quai) et a la sortie(apres la congelation)
            $table->decimal('poids_entree',10, 2)->nullable();
            $table->decimal('poids_sortie',10, 2)->nullable();

            // Ecart freinte = % perte de poids (calcule automatiquement)
            $table->decimal('ecart_freinte', 5, 2)->nullable();

            // Equipe de manutention (noms des dockers)
            $table->string('equipe_manutention')->nullable();

            // Statut actuel du lot dans le circuit
            $table->enum('statut', [
                'en_congelation',
                'en_stock',
                'sorti'

            ])->default('en_congelation');
        });
    }


    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Annule la migration :supprime les colonnes ajoutee
            $table->dropColumn([
                'nb_filets', 'nb_poissons', 'nb_bacs',
                'poids_entree', 'poids_sortie', 'ecart_freinte',
                'equipe_manutention', 'statut'

            ]);
        });
    }
};
