<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archives_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();            // bon_entree, bon_sortie, facture
            $table->string('cle', 120);                       // identifie le document (lots / sorties concernés)
            $table->unsignedInteger('sequence');              // numéro d'ordre par type
            $table->string('numero', 20)->index();            // BE-0001, BS-0001, FA-0001
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('client_nom');
            $table->string('resume')->nullable();             // lots concernés
            $table->decimal('poids_total', 12, 2)->default(0);
            $table->decimal('montant_total', 14, 2)->nullable();
            $table->json('contenu');                          // copie exacte du document au moment de l'émission
            $table->string('emis_par')->nullable();
            $table->unsignedInteger('impressions')->default(1);
            $table->timestamps();

            $table->unique(['type', 'cle']);
            $table->unique(['type', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archives_documents');
    }
};
