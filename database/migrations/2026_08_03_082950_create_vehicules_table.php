<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type');
            $table->string('immatriculation')->unique();
            $table->decimal('capacite_kg', 10, 2);
            $table->enum('statut', ['disponible', 'en_transit', 'maintenance'])->default('disponible');
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
