<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('categorie'); // congelation, transport, transport_ville, location
            $table->string('type')->nullable(); // avec_go, sans_go, null
            $table->string('designation'); // Nom du tarif
            $table->decimal('montant', 15, 2); // Valeur en Ar
            $table->json('details')->nullable(); // Infos supplémentaires
            $table->timestamps();

            // Index pour les recherches
            $table->index(['categorie', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
