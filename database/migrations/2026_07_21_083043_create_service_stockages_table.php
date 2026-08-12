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
        Schema::create('service_stockages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained('lots')->onDelete('cascade');
            $table->foreignId('chamre_froide_id')->constrained('chambre_froides')->onDelete('cascade');
            $table->date('date_entree');
            $table->date('date_sortie')->nullable();
            $table->decimal('cout', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_stockages');
    }
};
