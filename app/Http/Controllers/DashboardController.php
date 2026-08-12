<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lot;
use App\Models\Tunnel;
use App\Models\ChambreFroide;
use App\Models\Camion;
use App\Models\Client;
use App\Models\Produit;
use App\Models\ServiceCongelation;
use App\Models\ServiceStockage;

class DashboardController extends Controller
{
    public function index()
    {
        // ===== Compteurs globaux =====
        $totalLots = Lot::count();
        $totalClients = Client::count();
        $totalTunnels = Tunnel::count();
        $totalChambres = ChambreFroide::count();
        $totalProduits = Produit::count();

        // ===== KPI 1 : Lots actifs (pas encore sortis) =====
        $lotsActifsCollection = Lot::where('statut', '!=', 'sorti')->get();
        $lotsActifs = $lotsActifsCollection->count();

        // ===== KPI 2 : Kg total actuellement en stock =====
        $poidsStock = $lotsActifsCollection->sum(function ($lot) {
            return $lot->poids_sortie ?? $lot->poids_entree ?? $lot->quantite;
        });

        // ===== KPI 3 : Lots sortis =====
        $lotsSortis = Lot::where('statut', 'sorti')->count();

        // ===== KPI 4 : Occupation par tunnel (congelation en cours) =====
        $tunnels = Tunnel::all()->map(function ($tunnel) {
            $chargeKg = ServiceCongelation::where('tunnel_id', $tunnel->id)
                ->whereNull('date_fin')
                ->with('lot')
                ->get()
                ->sum(fn($sc) => $sc->lot->poids_entree ?? $sc->lot->quantite);

            $capaciteKg = $tunnel->capacite_tonnes * 1000; // tonnes -> kg
            $pourcentage = $capaciteKg > 0 ? round(($chargeKg / $capaciteKg) * 100) : 0;

            return [
                'nom' => $tunnel->nom,
                'charge_kg' => $chargeKg,
                'capacite_kg' => $capaciteKg,
                'pourcentage' => min($pourcentage, 100),
            ];
        });

        // ===== KPI 5 : Occupation par chambre froide (stockage en cours) =====
        $chambres = ChambreFroide::all()->map(function ($chambre) {
            $chargeKg = ServiceStockage::where('chamre_froide_id', $chambre->id)
                ->whereNull('date_sortie')
                ->with('lot')
                ->get()
                ->sum(fn($ss) => $ss->lot->poids_sortie ?? $ss->lot->quantite);

            $capaciteKg = $chambre->capacite_tonnes * 1000;
            $pourcentage = $capaciteKg > 0 ? round(($chargeKg / $capaciteKg) * 100) : 0;

            return [
                'nom' => $chambre->nom,
                'charge_kg' => $chargeKg,
                'capacite_kg' => $capaciteKg,
                'pourcentage' => min($pourcentage, 100),
            ];
        });

        // ===== KPI 6 : Alertes (lots en tunnel depuis plus de 24h) =====
        $alertesTunnel = ServiceCongelation::whereNull('date_fin')
            ->where('date_debut', '<=', now()->subHours(24))
            ->with('lot')
            ->get();

        // ===== Activite recente (uniquement les lots pas encore sortis) =====
        $derniersLots = Lot::with(['client'])
                            ->where('statut', '!=', 'sorti')
                            ->latest()
                            ->get();

        // Envoie toutes ces donnees a la vue dashboard
        return view('dashboard', compact(
            'totalLots',
            'totalClients',
            'totalTunnels',
            'totalChambres',
            'totalProduits',
            'lotsActifs',
            'poidsStock',
            'lotsSortis',
            'tunnels',
            'chambres',
            'alertesTunnel',
            'derniersLots'
        ));
    }
}
