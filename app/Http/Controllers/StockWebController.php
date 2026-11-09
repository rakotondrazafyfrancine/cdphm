<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use App\Models\Produit;
use App\Models\ChambreFroide;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StockWebController extends Controller
{
    public function index()
    {
        // Lots en stock (chambres uniquement)
        $lots = Lot::whereNotNull('chambre_id')
            ->where('statut', 'en_stock')
            ->with(['client', 'chambre', 'sortiesChambre'])
            ->latest()
            ->get();

        $totalPoids = $lots->sum('poids_entree');
        $totalLots = $lots->count();
        $totalClients = $lots->pluck('client_id')->unique()->count();

        // Pour le formulaire d'ajout
        $clients = Client::all();
        $produits = Produit::all();
        $chambres = ChambreFroide::all();
        return view('stocks.index', compact(
            'lots',
            'totalPoids',
            'totalLots',
            'totalClients',
            'clients',
            'produits',
            'chambres'
        ));
    }

    public function show($id)
    {
        $lot = Lot::with(['client', 'chambre'])->findOrFail($id);

        if ($lot->statut === 'sorti' && $lot->date_entree_chambre && $lot->date_sortie) {
            $jours = \Carbon\Carbon::parse($lot->date_entree_chambre)
                        ->diffInDays($lot->date_sortie) + 1;
            $tarifChambre = 25;
            $montant = $lot->poids_entree * $jours * $tarifChambre;
        } else {
            $jours = null;
            $montant = null;
            $tarifChambre = 25;
        }

        return view('stocks.show', compact('lot', 'jours', 'montant', 'tarifChambre'));
    }

}
