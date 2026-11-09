<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journaux', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_pseudo')->nullable();
            $table->string('action', 30)->index();   // creation, modification, suppression, connexion, deconnexion, echec_connexion
            $table->string('modele')->nullable()->index();
            $table->unsignedBigInteger('modele_id')->nullable();
            $table->string('description');
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux');
    }
};
