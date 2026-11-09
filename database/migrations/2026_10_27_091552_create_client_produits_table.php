<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clients', 'contact')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('contact')->nullable();
            });
        }

        Schema::create('client_produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->timestamps();
            $table->unique(['client_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_produits');
    }
};
