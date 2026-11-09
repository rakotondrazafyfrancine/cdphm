<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->string('entree_ref', 40)->nullable()->index()->after('client_id');
        });

        // Lots déjà enregistrés : ceux d'un même client créés à moins de 5 secondes d'écart
        // viennent de la même entrée (même validation du formulaire).
        $precedent = null;
        $ref = null;
        $groupes = [];

        foreach (DB::table('lots')->orderBy('client_id')->orderBy('created_at')->orderBy('id')->get(['id', 'client_id', 'created_at']) as $lot) {
            $instant = strtotime((string) $lot->created_at);

            if ($precedent && $precedent['client'] === $lot->client_id && abs($instant - $precedent['instant']) <= 5) {
                $groupes[$ref][] = $lot->id;
            } else {
                $ref = 'E' . Str::upper(Str::random(8));
                $groupes[$ref] = [$lot->id];
            }

            $precedent = ['client' => $lot->client_id, 'instant' => $instant];
        }

        foreach ($groupes as $reference => $ids) {
            if (count($ids) > 1) {
                DB::table('lots')->whereIn('id', $ids)->update(['entree_ref' => $reference]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropIndex(['entree_ref']);
            $table->dropColumn('entree_ref');
        });
    }
};
