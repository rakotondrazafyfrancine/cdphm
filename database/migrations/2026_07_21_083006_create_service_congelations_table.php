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
        Schema::create('service_congelations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained('lots')->onDelete('cascade');
            $table->foreignId('tunnel_id')->constrained('tunnels')->onDelete('cascade');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->decimal('cout', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_congelations');
    }
};
