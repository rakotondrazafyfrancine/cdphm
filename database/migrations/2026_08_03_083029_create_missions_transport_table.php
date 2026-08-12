<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicule_id')->constrained()->onDelete('cascade');
            $table->string('chauffeur');
            $table->string('chauffeur_tel');
            $table->string('chauffeur_cin')->nullable();
            $table->string('destination');
            $table->decimal('poids', 10, 2);
            $table->enum('option_carburant', ['avec_go', 'sans_go']);
            $table->decimal('cout_estime', 15, 2);
            $table->enum('statut', ['en_attente', 'validee', 'terminee'])->default('en_attente');
            $table->timestamps();

            $table->index('statut');
            $table->index('destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions_transport');
    }
};
