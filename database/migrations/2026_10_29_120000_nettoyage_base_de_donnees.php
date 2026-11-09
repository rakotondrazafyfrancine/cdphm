<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retire ce que l'application n'utilise plus : anciennes tables de transport, colonnes inutilisées de « lots »,
 * tarifs de transport/location, sessions d'invités et lignes de connexion du journal.
 * Une sauvegarde est dans database/sauvegardes/ (non rétablie automatiquement par down()).
 */
return new class extends Migration
{
    private const TABLES = [
        'vehicules', 'mission_transports', 'missions_transport', 'service_transports',
        'service_congelations', 'service_stockages', 'camions', 'trajets',
    ];

    private const COLONNES_LOTS = [
        'destination', 'transport_avec_go', 'frais_transport', 'frais_congelation', 'frais_total', 'jours_chambre',
        'penalite_tunnel', 'responsabilite_dep', 'categorie', 'equipe_manutention', 'nb_filets', 'nb_poissons',
        'nb_bacs', 'etape', 'origine',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        $existantes = array_values(array_filter(self::COLONNES_LOTS, fn ($c) => Schema::hasColumn('lots', $c)));
        if ($existantes) {
            Schema::table('lots', fn (Blueprint $t) => $t->dropColumn($existantes));
        }

        // seuls les tarifs Tunnel (congélation) et Chambre (stock) sont gérés
        DB::table('tarifs')->whereNotIn('designation', ['Tunnel', 'Chambre'])->delete();

        // sessions d'invités et connexions de test
        DB::table('sessions')->whereNull('user_id')->delete();
        DB::table('journaux')->whereIn('action', ['connexion', 'deconnexion', 'echec_connexion'])->delete();
    }

    public function down(): void
    {
        // Irréversible : restaurer la sauvegarde database/sauvegardes/*.sql si besoin.
    }
};
