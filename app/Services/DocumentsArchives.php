<?php

namespace App\Services;

use App\Models\Archive;
use Illuminate\Support\Collection;

/**
 * Fabrique les bons et factures et les archive au moment où le magasinier les imprime (première ouverture) ;
 * toute ré-ouverture affiche le même document, marqué « Duplicata ».
 */
class DocumentsArchives
{
    // ------------------------------------------------------------------ construction

    /** @return array{0: Archive, 1: bool} */
    public static function bonEntree(Collection $lots, string $cle, bool $ouverture = true): array
    {
        return Archives::obtenir('bon_entree', $cle, function () use ($lots) {
            $client = $lots->first()->client;
            $lignes = $lots->map(fn ($l) => [
                'numero' => $l->numero,
                'produit' => $l->espece ?? 'N/A',
                'poids' => (float) $l->poids_entree,
                'type' => $l->chambre_id ? 'Chambre' : 'Tunnel',
                'emplacement' => $l->chambre?->nom ?? $l->tunnel?->nom ?? 'Non assigné',
                'statut' => $l->chambre_id ? 'En stock' : 'En congélation',
            ])->values()->all();

            return [
                'client_id' => $client->id,
                'client_nom' => $client->nom,
                'resume' => $lots->pluck('numero')->implode(' · '),
                'poids_total' => $lots->sum('poids_entree'),
                'montant_total' => null,
                'contenu' => ['lignes' => $lignes],
            ];
        }, $ouverture);
    }

    /** @return array{0: Archive, 1: bool} */
    public static function facture(Collection $chambre, Collection $tunnel, bool $ouverture = true): array
    {
        $lignes = self::lignes($chambre, $tunnel);
        $statuts = $lignes->pluck('statut')->unique();

        $statut = $statuts->contains('en_attente_paiement')
            ? ($statuts->count() === 1 ? 'EN ATTENTE DE PAIEMENT' : 'PAIEMENT PARTIEL')
            : 'PAYEE';

        $cle = 'facture:c' . $chambre->pluck('id')->sort()->implode(',') . ';t' . $tunnel->pluck('id')->sort()->implode(',') . ';' . $statut;
        $client = $chambre->merge($tunnel)->first()->lot->client;

        return Archives::obtenir('facture', $cle, fn () => [
            'client_id' => $client->id,
            'client_nom' => $client->nom,
            'resume' => $lignes->pluck('numero')->implode(' · '),
            'poids_total' => $lignes->sum('poids_sorti'),
            'montant_total' => $lignes->sum('montant'),
            'contenu' => ['lignes' => self::archivables($lignes), 'total' => $lignes->sum('montant'), 'statut' => $statut],
        ], $ouverture);
    }

    /** Bon de sortie : seulement quand toutes les sorties sont effectuées, sinon null. @return array{0: Archive, 1: bool}|null */
    public static function bonSortie(Collection $chambre, Collection $tunnel, bool $ouverture = true): ?array
    {
        $lignes = self::lignes($chambre, $tunnel);
        if (! $lignes->every(fn ($l) => $l['statut'] === Sorties::EFFECTUEE)) {
            return null;
        }

        $cle = 'sortie:c' . $chambre->pluck('id')->sort()->implode(',') . ';t' . $tunnel->pluck('id')->sort()->implode(',');
        $client = $chambre->merge($tunnel)->first()->lot->client;

        return Archives::obtenir('bon_sortie', $cle, fn () => [
            'client_id' => $client->id,
            'client_nom' => $client->nom,
            'resume' => $lignes->pluck('numero')->implode(' · '),
            'poids_total' => $lignes->sum('poids_sorti'),
            'montant_total' => $lignes->sum('montant'),
            'contenu' => ['lignes' => self::archivables($lignes)],
        ], $ouverture);
    }

    public static function lignes(Collection $chambre, Collection $tunnel): Collection
    {
        return $chambre->map(fn ($x) => Sorties::ligne($x))
            ->concat($tunnel->map(fn ($x) => Sorties::ligne($x)))
            ->sortBy('numero')->values();
    }

    private static function archivables(Collection $lignes): array
    {
        return $lignes->map(function ($l) {
            foreach (['date_entree', 'date_sortie'] as $champ) {
                if (isset($l[$champ]) && $l[$champ] instanceof \DateTimeInterface) {
                    $l[$champ] = $l[$champ]->format('Y-m-d H:i:s');
                }
            }

            return $l;
        })->values()->all();
    }
}
