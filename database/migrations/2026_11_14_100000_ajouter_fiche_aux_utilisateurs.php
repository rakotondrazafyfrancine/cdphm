<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // fiche de renseignements de l'agent : nom, prénom, N° IM (matricule), département, fonction
            $table->string('nom')->nullable()->after('pseudo');
            $table->string('prenom')->nullable()->after('nom');
            $table->string('matricule', 30)->nullable()->after('prenom');
            $table->string('departement')->nullable()->after('matricule');
            $table->string('fonction')->nullable()->after('departement');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom', 'matricule', 'departement', 'fonction']);
        });
    }
};
