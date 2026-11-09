<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\SortieChambre;
use App\Models\SortieTunnel;
use Carbon\Carbon;

/**
 * Logique des sorties (facture, paiement, sortie physique), partagée par les écrans et les actions groupées.
 */
class Sorties
{
    public const EN_ATTENTE = 'en_attente_paiement';

    public const PAYE = 'paye';

    public const EFFECTUEE = 'sortie_effectuee';

    /** Sortie totale d'un lot en tunnel : facture créée, en attente de paiement. */
    public static function sortirTunnel(Lot $lot): SortieTunnel
    {
        $heures = $lot->created_at ? now()->diffInHours($lot->created_at, absolute: true) : 0;
        $jours = 1;
        $tarif = (float) ($lot->tarif_applique ?? Lot::tarifTunnel());

        return SortieTunnel::create([
            'lot_id' => $lot->id,
            'poids_sorti' => $lot->poids_entree,
            'heures' => $heures,
            'jours' => $jours,
            'montant' => $lot->poids_entree * $jours * $tarif,
            'tarif_applique' => $tarif,
            'emplacement' => $lot->tunnel?->nom,
            'date_entree' => $lot->created_at,
            'statut' => self::EN_ATTENTE,
        ]);
    }

    /** Sortie (partielle ou totale) d'un lot en chambre : facture créée, en attente de paiement. */
    public static function sortirChambre(Lot $lot, float $poids): SortieChambre
    {
        [$jours, $tarif] = self::joursEtTarifChambre($lot);

        return SortieChambre::create([
            'lot_id' => $lot->id,
            'poids_sorti' => $poids,
            'jours' => $jours,
            'montant' => $poids * $jours * $tarif,
            'tarif_applique' => $tarif,
            'emplacement' => $lot->chambre?->nom,
            'date_entree' => $lot->date_entree_chambre ?? $lot->created_at,
            'statut' => self::EN_ATTENTE,
        ]);
    }

    /** @return array{0:int,1:float} jours de stockage (minimum 1) et tarif appliqué */
    public static function joursEtTarifChambre(Lot $lot): array
    {
        $jours = 0;
        if ($lot->date_entree_chambre) {
            $jours = Carbon::parse($lot->date_entree_chambre)->diffInDays(now(), absolute: true);
            $jours = max((int) ceil($jours), 1);
        }

        return [$jours, (float) ($lot->tarif_applique ?? Lot::tarifChambre())];
    }

    /** Vrai si le lot a déjà une facture non terminée (à payer ou à sortir). */
    public static function aUneSortieEnCours(Lot $lot): bool
    {
        return $lot->sortiesChambre()->whereIn('statut', [self::EN_ATTENTE, self::PAYE])->exists()
            || $lot->sortiesTunnel()->whereIn('statut', [self::EN_ATTENTE, self::PAYE])->exists();
    }

    /** Sortie physique d'un lot de tunnel (la facture doit être payée). */
    public static function confirmerTunnel(SortieTunnel $sortie): void
    {
        $lot = $sortie->lot;
        $lot->statut = 'sorti';
        $lot->date_sortie = now();
        $lot->tunnel_id = null;
        $lot->montant_tunnel = $sortie->montant;
        $lot->save();

        $sortie->statut = self::EFFECTUEE;
        $sortie->save();
    }

    /** Sortie physique (partielle ou totale) d'un lot de chambre (la facture doit être payée). */
    public static function confirmerChambre(SortieChambre $sortie): void
    {
        $lot = $sortie->lot;

        $nouveauRestant = round(($lot->poids_restant ?? $lot->poids_entree) - $sortie->poids_sorti, 2);
        $lot->poids_restant = $nouveauRestant;

        if ($nouveauRestant <= 0) {
            $lot->statut = 'sorti';
            $lot->date_sortie = now();
            $lot->chambre_id = null;
            $lot->date_entree_chambre = null;
        }
        $lot->save();

        $sortie->statut = self::EFFECTUEE;
        $sortie->reste_apres = max($nouveauRestant, 0);
        $sortie->save();
    }

    /**
     * Informations complètes d'une sortie pour les factures et bons de sortie.
     *
     * @return array<string, mixed>
     */
    public static function ligne(SortieChambre|SortieTunnel $sortie): array
    {
        $lot = $sortie->lot;
        $chambre = $sortie instanceof SortieChambre;

        $dejaSorti = $chambre
            ? (float) SortieChambre::where('lot_id', $lot->id)->where('statut', self::EFFECTUEE)->where('id', '<', $sortie->id)->sum('poids_sorti')
            : 0.0;

        if ($chambre) {
            $reste = $sortie->reste_apres !== null
                ? (float) $sortie->reste_apres
                : ($sortie->statut === self::EFFECTUEE
                    ? (float) ($lot->statut === 'sorti' ? 0 : ($lot->poids_restant ?? 0))
                    : max(0, (float) ($lot->poids_restant ?? $lot->poids_entree) - (float) $sortie->poids_sorti));
        } else {
            $reste = 0.0;
        }

        return [
            'id' => $sortie->id,
            'type' => $chambre ? 'Chambre' : 'Tunnel',
            'numero' => $lot->numero,
            'produit' => $lot->espece ?? '—',
            'emplacement' => $sortie->emplacement ?? ($chambre ? $lot->chambre?->nom : $lot->tunnel?->nom) ?? '—',
            'date_entree' => $sortie->date_entree ?? ($chambre ? ($lot->date_entree_chambre ?? $lot->created_at) : $lot->created_at),
            'poids_entree' => (float) $lot->poids_entree,
            'deja_sorti' => $dejaSorti,
            'poids_sorti' => (float) $sortie->poids_sorti,
            'reste' => $reste,
            'partielle' => $chambre && $reste > 0,
            'jours' => (int) $sortie->jours,
            'heures' => $chambre ? null : (float) $sortie->heures,
            'tarif' => (float) ($sortie->tarif_applique ?? 0),
            'montant' => (float) $sortie->montant,
            'statut' => $sortie->statut,
            'date_sortie' => $sortie->updated_at,
        ];
    }
}
