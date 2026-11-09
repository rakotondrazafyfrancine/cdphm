<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorties_tunnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->decimal('poids_sorti', 10, 2);
            $table->decimal('heures', 8, 2);
            $table->integer('jours');
            $table->decimal('montant', 12, 2);
            $table->string('statut')->default('en_attente_paiement');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorties_tunnel');
    }
};
