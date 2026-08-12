<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lot')->unique();
            $table->decimal('quantite', 10, 2);
            $table->date('date_entree');
            $table->enum('origine',['frais', 'deja_congele']);
            $table->enum('etape',['congelation','mise_en_sac','stockage','sortie'])->default('congelation');
            $table->date('date_sortie')->nullable();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
