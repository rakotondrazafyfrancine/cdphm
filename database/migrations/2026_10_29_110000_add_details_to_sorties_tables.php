<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sorties_chambre', 'sorties_tunnel'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->string('emplacement')->nullable();           // nom du tunnel / de la chambre au moment de la sortie
                $t->dateTime('date_entree')->nullable();          // date d'entrée du lot dans cet emplacement
                if ($table === 'sorties_chambre') {
                    $t->decimal('reste_apres', 12, 2)->nullable(); // poids restant en chambre après la sortie
                }
            });
        }

        // Sorties déjà enregistrées : on reprend ce que le lot indique encore.
        foreach (DB::table('sorties_chambre')->get(['id', 'lot_id']) as $s) {
            $lot = DB::table('lots')->where('id', $s->lot_id)->first();
            if (! $lot) {
                continue;
            }
            $nom = $lot->chambre_id ? DB::table('chambre_froides')->where('id', $lot->chambre_id)->value('nom') : null;
            DB::table('sorties_chambre')->where('id', $s->id)->update(['emplacement' => $nom, 'date_entree' => $lot->date_entree_chambre ?? $lot->created_at]);
        }

        foreach (DB::table('sorties_tunnel')->get(['id', 'lot_id']) as $s) {
            $lot = DB::table('lots')->where('id', $s->lot_id)->first();
            if (! $lot) {
                continue;
            }
            $nom = $lot->tunnel_id ? DB::table('tunnels')->where('id', $lot->tunnel_id)->value('nom') : null;
            DB::table('sorties_tunnel')->where('id', $s->id)->update(['emplacement' => $nom, 'date_entree' => $lot->created_at]);
        }
    }

    public function down(): void
    {
        Schema::table('sorties_chambre', fn (Blueprint $t) => $t->dropColumn(['emplacement', 'date_entree', 'reste_apres']));
        Schema::table('sorties_tunnel', fn (Blueprint $t) => $t->dropColumn(['emplacement', 'date_entree']));
    }
};
