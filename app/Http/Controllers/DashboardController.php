<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use App\Models\Tunnel;
use App\Models\ChambreFroide;
use App\Models\Produit;

class DashboardController extends Controller
{
    public function index()
    {
        $enChambre = Lot::where('statut', 'en_stock')->get();
        $enTunnel  = Lot::where('statut', 'en_congelation')->get();

        $lotsActifs   = $enChambre->count() + $enTunnel->count();
        $totalClients = Client::count();

        // Chambre : poids restant (sorties partielles), sinon poids d'entrée
        $poidsStock = $enChambre->sum(fn($lot) => $lot->poids_restant ?? $lot->poids_entree);

        // Tunnel : poids d'entrée uniquement
        $poidsTunnel = $enTunnel->sum(fn($lot) => $lot->poids_entree);

        $totalTunnels  = Tunnel::count();
        $totalChambres = ChambreFroide::count();
        $totalProduits = Produit::count();
        $lotsSortis    = Lot::where('statut', 'sorti')->count();

        // Activité récente : uniquement les lots encore présents (les lots sortis disparaissent)
        $derniersLots = Lot::with('client')
            ->where('statut', '!=', 'sorti')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'lotsActifs',
            'totalClients',
            'poidsStock',
            'poidsTunnel',
            'totalTunnels',
            'totalChambres',
            'totalProduits',
            'lotsSortis',
            'derniersLots'
        ));
    }
}
