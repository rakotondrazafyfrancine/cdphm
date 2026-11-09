<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Parametre;
use Illuminate\Support\Facades\DB;

class Archives
{
    /**
     * Retrouve l'archive d'un document déjà émis, ou la crée à la première émission.
     * Un document archivé est toujours réaffiché tel qu'il était (même numéro, même date, mêmes montants).
     *
     * @param  callable(): array  $construire  doit renvoyer ['client_id', 'client_nom', 'resume', 'poids_total', 'montant_total', 'contenu' => [...]]
     * @return array{0: Archive, 1: bool} l'archive et « duplicata » (vrai si le document a déjà été imprimé)
     */
    public static function obtenir(string $type, string $cle, callable $construire, bool $ouverture = true, ?string $emisPar = null): array
    {
        $existante = Archive::where('type', $type)->where('cle', $cle)->first();
        if ($existante) {
            if (! $ouverture) {
                return [$existante, false];
            }

            // le premier affichage d'un document archivé automatiquement est l'original ; les suivants sont des duplicatas
            $duplicata = $existante->impressions > 0;
            $existante->increment('impressions');

            return [$existante, $duplicata];
        }

        return DB::transaction(function () use ($type, $cle, $construire, $ouverture, $emisPar) {
            $donnees = $construire();
            $sequence = (int) Archive::where('type', $type)->lockForUpdate()->max('sequence') + 1;
            $prefixe = Archive::TYPES[$type][1];

            $archive = Archive::create([
                'type' => $type,
                'cle' => $cle,
                'sequence' => $sequence,
                'numero' => $prefixe . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'client_id' => $donnees['client_id'] ?? null,
                'client_nom' => $donnees['client_nom'],
                'resume' => $donnees['resume'] ?? null,
                'poids_total' => $donnees['poids_total'] ?? 0,
                'montant_total' => $donnees['montant_total'] ?? null,
                'contenu' => $donnees['contenu'] + ['societe' => self::societe()],
                'emis_par' => $emisPar ?? auth()->user()?->pseudo,
                'impressions' => $ouverture ? 1 : 0,
            ]);

            return [$archive, false];
        });
    }

    /** Coordonnées de la société telles qu'elles sont au moment de l'émission. */
    private static function societe(): array
    {
        return [
            'nom' => Parametre::lire('nom', 'CDPHM'),
            'logo' => Parametre::lire('logo'),
            'contact' => Parametre::lire('contact'),
            'lieu' => Parametre::lire('lieu'),
        ];
    }
}
