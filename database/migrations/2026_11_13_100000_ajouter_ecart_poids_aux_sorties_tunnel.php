<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorties_tunnel', function (Blueprint $table) {
            // poids noté à l'entrée et écart constaté à la pesée de sortie (positif : plus lourd, négatif : manque)
            $table->decimal('poids_enregistre', 10, 2)->nullable()->after('poids_sorti');
            $table->decimal('ecart', 10, 2)->default(0)->after('poids_enregistre');
        });
    }

    public function down(): void
    {
        Schema::table('sorties_tunnel', function (Blueprint $table) {
            $table->dropColumn(['poids_enregistre', 'ecart']);
        });
    }
};
