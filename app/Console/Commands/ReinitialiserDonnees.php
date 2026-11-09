<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReinitialiserDonnees extends Command
{
    protected $signature = 'donnees:reinitialiser
        {--simulation : Affiche seulement ce qui serait supprimé, sans rien toucher}
        {--sans-sauvegarde : Ne fait pas de sauvegarde avant de vider (déconseillé)}
        {--force : Ne demande pas de confirmation}';

    protected $description = 'Vide les données de test (clients, produits, lots, sorties, factures, journal, notifications) et garde la configuration';

    /** Tables vidées : toutes les données saisies en travaillant. */
    private const A_VIDER = [
        'sorties_tunnel', 'sorties_chambre', 'client_produits', 'lots', 'clients', 'produits',
        'notifications', 'journaux', 'archives_documents', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches',
    ];

    /** Tables conservées : comptes, tunnels, chambres, tarifs, paramètres. */
    private const CONSERVEES = ['users', 'tunnels', 'chambre_froides', 'tarifs', 'parametres'];

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Réinitialisation des données de test');

        $this->table(['Sera vidé', 'Lignes'], collect(self::A_VIDER)
            ->filter(fn ($t) => Schema::hasTable($t))
            ->map(fn ($t) => [$t, DB::table($t)->count()])->all());

        $this->table(['Sera conservé', 'Lignes'], collect(self::CONSERVEES)
            ->filter(fn ($t) => Schema::hasTable($t))
            ->map(fn ($t) => [$t, DB::table($t)->count()])->all());

        if ($this->option('simulation')) {
            $this->components->warn('Simulation : rien n\'a été modifié.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Vider ces données ? Cette action est définitive (une sauvegarde est faite avant).', false)) {
            $this->components->warn('Annulé : rien n\'a été modifié.');

            return self::FAILURE;
        }

        if (! $this->option('sans-sauvegarde') && ! $this->sauvegarder()) {
            $this->components->error('La sauvegarde a échoué : rien n\'a été supprimé. Relance avec --sans-sauvegarde si tu veux continuer quand même.');

            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();
        foreach (self::A_VIDER as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        $this->components->info('Données de test supprimées. Le prochain lot sera LOT-0001.');

        return self::SUCCESS;
    }

    /** Sauvegarde la base dans database/sauvegardes/ avec mysqldump. */
    private function sauvegarder(): bool
    {
        $connexion = config('database.connections.' . config('database.default'));

        if (($connexion['driver'] ?? null) !== 'mysql') {
            $this->components->warn('Sauvegarde automatique disponible seulement pour MySQL.');

            return false;
        }

        $dossier = database_path('sauvegardes');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }
        $fichier = $dossier . DIRECTORY_SEPARATOR . 'cdphm_avant_reinitialisation_' . date('Y-m-d_Hi') . '.sql';

        $executable = collect([
            'mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp83\\mysql\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
        ])->first(fn ($c) => $c === 'mysqldump' ? $this->commandeExiste('mysqldump') : is_file($c));

        if (! $executable) {
            $this->components->warn('mysqldump introuvable.');

            return false;
        }

        $commande = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s --single-transaction %s > "%s"',
            $executable,
            escapeshellarg($connexion['host']),
            escapeshellarg((string) $connexion['port']),
            escapeshellarg($connexion['username']),
            filled($connexion['password']) ? '--password=' . escapeshellarg($connexion['password']) : '',
            escapeshellarg($connexion['database']),
            $fichier
        );

        exec($commande, $sortie, $code);

        if ($code !== 0 || ! is_file($fichier) || filesize($fichier) < 1000) {
            @unlink($fichier);

            return false;
        }

        $this->components->twoColumnDetail('Sauvegarde', $fichier);

        return true;
    }

    private function commandeExiste(string $nom): bool
    {
        $test = PHP_OS_FAMILY === 'Windows' ? "where $nom" : "command -v $nom";
        exec($test . ' 2>&1', $sortie, $code);

        return $code === 0;
    }
}
