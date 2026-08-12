<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Tarif;

class CalculationService
{
    const TARIF_TUNNEL = 450;
    const TARIF_CHAMBRE = 25;

    public function calculateCongelationFees(Lot $lot): array
    {
        $tarifs = Tarif::where('categorie', 'congelation')->get()->pluck('montant', 'designation');
        $poids = $lot->poids_sortie ?? $lot->poids_entree;

        $fees = [];
        $fees['rapide'] = $poids * ($tarifs['Rapide'] ?? self::TARIF_TUNNEL);
        $fees['societe'] = $poids * ($tarifs['Société'] ?? 0);

        if (in_array($lot->categorie, ['poisson', 'crabe'])) {
            $fees['collecteur'] = $poids * ($tarifs['Collecteur/Maréyeur/Pêcheurs (Poisson et Crabe)'] ?? 25);
        } elseif (in_array($lot->categorie, ['crevette', 'langouste'])) {
            $fees['collecteur'] = $poids * ($tarifs['Collecteur/Maréyeur/Pêcheurs (Crevettes et Langoustes)'] ?? 50);
        } else {
            $fees['collecteur'] = 0;
        }

        if ($lot->categorie === 'crabe' && $lot->avec_traitement) {
            $fees['eau'] = $poids * ($tarifs['Participation eau (crabe avec traitement)'] ?? 100);
        } elseif ($lot->categorie === 'crabe' && !$lot->avec_traitement) {
            $fees['eau'] = $poids * ($tarifs['Participation eau (crabe sans traitement)'] ?? 200);
        } else {
            $fees['eau'] = $poids * ($tarifs['Participation eau (poissons, crevettes, ...)'] ?? 50);
        }

        $fees['total'] = array_sum($fees);
        return $fees;
    }

    public function calculateTransportCost(string $destination, bool $avecGO, float $poids, ?float $tarifLibre = null): array
    {
        // Exception spéciale Camion 4T
        if ($destination === 'Mahajanga - Tamanarivo' && $poids <= 4000) {
            return [
                'tarif' => 350,
                'poids' => $poids,
                'total' => round($poids * 350),
                'with_go' => $avecGO,
                'destination' => $destination,
                'exception' => 'Camion 4T - Tarif spécial 350 Ar/Kg',
                'est_valide' => true
            ];
        }

        if ($tarifLibre && $tarifLibre > 0) {
            return [
                'tarif' => $tarifLibre,
                'poids' => $poids,
                'total' => round($poids * $tarifLibre),
                'with_go' => $avecGO,
                'destination' => $destination,
                'exception' => 'Tarif personnalisé',
                'est_valide' => true
            ];
        }

        $type = $avecGO ? 'avec_go' : 'sans_go';
        $tarif = Tarif::where('categorie', 'transport')
            ->where('type', $type)
            ->where('designation', 'LIKE', "%{$destination}%")
            ->first();

        if (!$tarif) {
            return ['error' => 'Tarif non trouvé', 'est_valide' => false];
        }

        return [
            'tarif' => $tarif->montant,
            'poids' => $poids,
            'total' => round($poids * $tarif->montant),
            'with_go' => $avecGO,
            'destination' => $destination,
            'exception' => null,
            'est_valide' => true
        ];
    }

    public function calculateTotalFees(Lot $lot): array
    {
        $result = [
            'congelation' => 0,
            'transport' => 0,
        ];

        // Congélation
        if ($lot->type_infrastructure === 'tunnel') {
            $result['congelation'] = $this->calculateCongelationFees($lot)['total'];
        }

        // Transport
        if ($lot->destination) {
            $transport = $this->calculateTransportCost(
                $lot->destination,
                $lot->transport_avec_go ?? true,
                $lot->poids_sortie ?? $lot->poids_entree,
                $lot->tarif_transport_libre ?? null
            );
            if ($transport['est_valide'] ?? false) {
                $result['transport'] = $transport['total'];
            }
        }

        $result['total'] = $result['congelation'] + $result['transport'];
        return $result;
    }
}
