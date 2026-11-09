<?php

namespace App\Support;

use App\Models\Lot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Même logique de filtre que RapportWebController, partagée par la page Filament,
 * ses widgets et les liens d'impression / d'export.
 */
class RapportFiltres
{
    /** Lots filtrés, sans relations ni tri : sert aux totaux calculés directement par la base. */
    public static function requete(?array $f): Builder
    {
        $f = $f ?? [];
        $query = Lot::query();

        if (! empty($f['date_debut'])) {
            $query->whereDate('created_at', '>=', $f['date_debut']);
        }
        if (! empty($f['date_fin'])) {
            $query->whereDate('created_at', '<=', $f['date_fin']);
        }
        if (! empty($f['client_id'])) {
            $query->where('client_id', $f['client_id']);
        }
        if (! empty($f['espece'])) {
            $query->where('espece', $f['espece']);
        }
        if (! empty($f['tunnel_id'])) {
            $query->where('tunnel_id', $f['tunnel_id']);
        }
        if (! empty($f['chambre_id'])) {
            $query->where('chambre_id', $f['chambre_id']);
        }
        if (! empty($f['mouvement'])) {
            $query->where('statut', $f['mouvement'] === 'sortie' ? 'sorti' : 'en_stock');
        }

        return $query;
    }

    public static function lots(?array $f): Builder
    {
        return self::requete($f)->with(['client', 'tunnel', 'chambre'])->orderByDesc('created_at');
    }

    /** @var array<string, array> totaux déjà calculés pendant la requête, par jeu de filtres */
    private static array $memoTotaux = [];

    /** Totaux du rapport en une seule requête SQL (au lieu de charger tous les lots en mémoire). */
    public static function totaux(?array $f): array
    {
        return self::$memoTotaux[md5(json_encode($f ?? []))] ??= (function () use ($f) {
            $r = self::requete($f)->selectRaw(
                "COUNT(*) AS entrees, COALESCE(SUM(poids_entree), 0) AS entrees_poids,
                 COALESCE(SUM(statut = 'sorti'), 0) AS sorties,
                 COALESCE(SUM(CASE WHEN statut = 'sorti' THEN poids_entree END), 0) AS sorties_poids,
                 COALESCE(SUM(statut = 'en_stock'), 0) AS chambre_lots,
                 COALESCE(SUM(CASE WHEN statut = 'en_stock' THEN COALESCE(poids_restant, poids_entree) END), 0) AS chambre_poids,
                 COALESCE(SUM(statut = 'en_congelation'), 0) AS tunnel_lots,
                 COALESCE(SUM(CASE WHEN statut = 'en_congelation' THEN poids_entree END), 0) AS tunnel_poids"
            )->first();

            return array_map(fn ($v) => (float) $v, $r->getAttributes());
        })();
    }

    /**
     * Poids entrés et sortis par mois (clés « Y-m »), calculés par la base.
     *
     * @return array{0: array<string, float>, 1: array<string, float>}
     */
    public static function parMois(?array $f): array
    {
        $entrees = self::requete($f)->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS mois, SUM(poids_entree) AS total")
            ->groupBy('mois')->pluck('total', 'mois')->map(fn ($v) => (float) $v)->all();
        $sorties = self::requete($f)->whereNotNull('date_sortie')->selectRaw("DATE_FORMAT(date_sortie, '%Y-%m') AS mois, SUM(poids_entree) AS total")
            ->groupBy('mois')->pluck('total', 'mois')->map(fn ($v) => (float) $v)->all();

        return [$entrees, $sorties];
    }

    /** @var array<string, Collection> lots déjà chargés pendant la requête, par jeu de filtres */
    private static array $memo = [];

    /** Lots filtrés, chargés une seule fois même si plusieurs widgets les demandent. */
    public static function donnees(?array $f): Collection
    {
        $cle = md5(json_encode($f ?? []));

        return self::$memo[$cle] ??= self::lots($f)->get();
    }

    /** Paramètres à passer aux routes d'impression et d'export CSV. */
    public static function parametres(?array $f): array
    {
        return array_filter($f ?? [], fn ($v) => filled($v));
    }

    public static function indicateurs(Collection $lots): array
    {
        $enChambre = $lots->where('statut', 'en_stock');
        $enTunnel = $lots->where('statut', 'en_congelation');
        $sortis = $lots->where('statut', 'sorti');

        return [
            'entrees' => $lots->count(),
            'entrees_poids' => (float) $lots->sum('poids_entree'),
            'sorties' => $sortis->count(),
            'sorties_poids' => (float) $sortis->sum('poids_entree'),
            'chambre_lots' => $enChambre->count(),
            'chambre_poids' => (float) $enChambre->sum(fn ($l) => $l->poids_restant ?? $l->poids_entree),
            'tunnel_lots' => $enTunnel->count(),
            'tunnel_poids' => (float) $enTunnel->sum('poids_entree'),
        ];
    }
}
