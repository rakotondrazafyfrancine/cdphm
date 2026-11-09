<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('sorties_chambre', function (Blueprint $table) {
        $table->decimal('tarif_applique', 10, 2)->nullable()->after('montant');
    });
    Schema::table('sorties_tunnel', function (Blueprint $table) {
        $table->decimal('tarif_applique', 10, 2)->nullable()->after('montant');
    });
}

public function down(): void
{
    Schema::table('sorties_chambre', fn($t) => $t->dropColumn('tarif_applique'));
    Schema::table('sorties_tunnel', fn($t) => $t->dropColumn('tarif_applique'));
}
};
