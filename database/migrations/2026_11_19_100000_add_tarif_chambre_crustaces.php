<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('tarifs')->where('designation', 'Chambre langouste/crabe')->exists()) {
            DB::table('tarifs')->insert([
                'categorie' => 'stock',
                'designation' => 'Chambre langouste/crabe',
                'montant' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Les lots de langouste et de crabe déjà en chambre froide passent au nouveau tarif
        DB::table('lots')->where('statut', 'en_stock')
            ->where(fn ($q) => $q->where('espece', 'like', '%langouste%')->orWhere('espece', 'like', '%crabe%'))
            ->update(['tarif_applique' => 50]);
    }

    public function down(): void
    {
        DB::table('tarifs')->where('designation', 'Chambre langouste/crabe')->delete();
    }
};
