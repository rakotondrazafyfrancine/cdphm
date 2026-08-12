<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //On modifie la table "users" existante pour y ajoute une colonne
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'receptionniste',
                'magasiner',
                'logistique',
                'comptable',
                'directeur'

            ])->default('receptionniste');

        });
    }

    public function down(): void
    {
        //Permet d'annuler la migration : supprime la colonne "role"
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');

        });
    }
};
