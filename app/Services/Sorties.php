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

    /**
     * Sortie totale d'un lot en tunnel, créée au moment du BON DE SORTIE (avant le passage à la caisse).
     *
     * Le poids est celui de la contre-pesée. Il figure sur le bon et c'est ce poids que
     * le client paie à la caisse. L'écart avec le poids enregistré est signalé aux responsables.
     */
    public static function sortirTunnel(Lot $lot, ?float $poidsReel = null): SortieTunnel
    {
        // les heures de congélation comptent depuis le démarrage du tunnel, pas depuis l'enregistrement du lot
        $debut = $lot->tunnel?->demarre_le ?? $lot->created_at;
        $heures = $debut ? now()->diffInHours($debut, absolute: true) : 0;
        $jours = 1;
        $tarif = (float) ($lot->tarif_applique ?? Lot::tarifTunnel());

        $enregistre = (float) $lot->poids_entree;
        $reel = $poidsReel !== null && $poidsReel > 0 ? round($poidsReel, 2) : $enregistre;
        $ecart = round($reel - $enregistre, 2);
        $facture = $reel;

        $sortie = SortieTunnel::create([
            'lot_id' => $lot->id,
            'poids_sorti' => $reel,
            'poids_enregistre' => $enregistre,
            'ecart' => $ecart,
            'heures' => $heures,
            'jours' => $jours,
            'montant' => $facture * $jours * $tarif,
            'tarif_applique' => $tarif,
            'emplacement' => $lot->tunnel?->nom,
            'date_entree' => $lot->created_at,
            'statut' => self::EN_ATTENTE,
        ]);

        if ($ecart != 0.0) {
            self::signalerEcart($lot, $sortie);
        }

        return $sortie;
    }

    /** Message si le tunnel du lot est démarré depuis moins de 24 h (donc impossible à ouvrir), sinon null. */
    public static function tunnelFerme(Lot $lot): ?string
    {
        $tunnel = $lot->tunnel;
        if (! $tunnel || $tunnel->peutEtreOuvert()) {
            return null;
        }

        return $tunnel->nom . ' a démarré le ' . $tunnel->demarre_le->format('d/m à H:i') . ' : il ne peut être ouvert qu\'à partir du ' . $tunnel->ouvrableLe()->format('d/m à H:i') . '.';
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

    /** Prévient tous les utilisateurs d'un écart de poids constaté à la contre-pesée de sortie d'un tunnel. */
    private static function signalerEcart(Lot $lot, SortieTunnel $sortie): void
    {
        $ecart = (float) $sortie->ecart;
        $kg = fn ($v) => number_format(abs($v), 2, ',', ' ') . ' kg';

        $notification = \Filament\Notifications\Notification::make()
            ->title($lot->numero . ' : écart de poids à la contre-pesée')
            ->body(collect([
                $lot->client?->nom,
                $lot->espece,
                'enregistré ' . $kg($sortie->poids_enregistre),
                'constaté ' . $kg($sortie->poids_sorti),
                $ecart > 0 ? '+' . $kg($ecart) . ' de plus que l’enregistrement' : '-' . $kg($ecart) . ' de moins que l’enregistrement, à justifier',
            ])->filter()->implode(' · '))
            ->{$ecart > 0 ? 'info' : 'warning'}()
            ->icon('lucide-scale')
            ->toDatabase();

        \App\Models\User::all()->each(fn ($u) => $u->notifyNow($notification));
    }

    /** Vrai si le lot a déjà une facture non terminée (à payer ou à sortir). */
    public static function aUneSortieEnCours(Lot $lot): bool
    {
        return $lot->sortiesChambre()->whereIn('statut', [self::EN_ATTENTE, self::PAYE])->exists()
            || $lot->sortiesTunnel()->whereIn('statut', [self::EN_ATTENTE, self::PAYE])->exists();
    }

    /** Sortie physique d'un lot de tunnel, une fois la facture payée à la caisse. */
    public static function confirmerTunnel(SortieTunnel $sortie): void
    {
        $lot = $sortie->lot;
        $tunnelId = $lot->tunnel_id;
        $lot->statut = 'sorti';
        $lot->date_sortie = now();
        $lot->tunnel_id = null;
        $lot->montant_tunnel = $sortie->montant;
        $lot->save();

        // dernier lot sorti : le tunnel est de nouveau libre pour un nouveau chargement
        \App\Models\Tunnel::find($tunnelId)?->libererSiVide();

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
            'poids_enregistre' => ! $chambre && $sortie->poids_enregistre !== null ? (float) $sortie->poids_enregistre : (float) $lot->poids_entree,
            'ecart' => $chambre ? 0.0 : (float) ($sortie->ecart ?? 0),
            'complement' => ! $chambre && $sortie->complement_de_id !== null,
            'poids_facture' => $chambre || (float) $sortie->tarif_applique * max((int) $sortie->jours, 1) <= 0
                ? (float) $sortie->poids_sorti
                : round((float) $sortie->montant / ((float) $sortie->tarif_applique * max((int) $sortie->jours, 1)), 2),
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
