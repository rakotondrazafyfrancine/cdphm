<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use Carbon\Carbon;

class RapportWebController extends Controller
{
    public function index()
    {
        // Mois actuel et mois précédent
        $moisActuel = Carbon::now()->startOfMonth();
        $moisPrecedent = Carbon::now()->subMonth()->startOfMonth();

        // Statistiques mois actuel
        $statsActuel = $this->getStatsMois($moisActuel);

        // Statistiques mois précédent
        $statsPrecedent = $this->getStatsMois($moisPrecedent);

        // Lots du mois actuel pour le tableau détaillé
        $lotsActuel = Lot::with(['client', 'produit'])
            ->whereYear('created_at', $moisActuel->year)
            ->whereMonth('created_at', $moisActuel->month)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lots du mois précédent pour le tableau détaillé
        $lotsPrecedent = Lot::with(['client', 'produit'])
            ->whereYear('created_at', $moisPrecedent->year)
            ->whereMonth('created_at', $moisPrecedent->month)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rapports.index', compact(
            'statsActuel', 'statsPrecedent',
            'lotsActuel', 'lotsPrecedent',
            'moisActuel', 'moisPrecedent'
        ));
    }

    private function getStatsMois(Carbon $debut)
    {
        $fin = $debut->copy()->endOfMonth();

        $lots = Lot::whereYear('created_at', $debut->year)
            ->whereMonth('created_at', $debut->month)
            ->get();

        return [
            'nb_lots'          => $lots->count(),
            'poids_total'      => $lots->sum('quantite'),
            'recettes_tunnel'  => $lots->sum('montant_tunnel'),
            'recettes_chambre' => $lots->sum('montant_chambre'),
            'total'            => $lots->sum('montant_tunnel') + $lots->sum('montant_chambre'),
            'lots_sortis'      => $lots->where('etape', 'sortie')->count(),
            'lots_en_stock'    => $lots->where('etape', '!=', 'sortie')->count(),
        ];
    }
}
