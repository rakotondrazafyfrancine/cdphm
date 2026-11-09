<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Index qui accélèrent les filtres, les tris et les recherches les plus utilisés. */
    private array $index = [
        'lots' => [['created_at'], ['date_sortie'], ['statut', 'created_at']],
        'sorties_tunnel' => [['statut'], ['created_at']],
        'sorties_chambre' => [['statut'], ['created_at']],
        'clients' => [['nom']],
        'produits' => [['nom']],
    ];

    public function up(): void
    {
        foreach ($this->index as $table => $listes) {
            foreach ($listes as $colonnes) {
                if (! Schema::hasTable($table) || Schema::hasIndex($table, $colonnes)) {
                    continue;
                }
                Schema::table($table, fn ($t) => $t->index($colonnes));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->index as $table => $listes) {
            foreach ($listes as $colonnes) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $colonnes)) {
                    Schema::table($table, fn ($t) => $t->dropIndex($colonnes));
                }
            }
        }
    }
};
