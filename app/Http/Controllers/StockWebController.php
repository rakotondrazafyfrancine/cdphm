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
            ->with(['client', 'chambre'])
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

    // Calcul des jours et du montant si le lot est sorti
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

    return view('stock.show', compact('lot', 'jours', 'montant', 'tarifChambre'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'produit_id' => 'required|exists:produits,id',
            'poids_entree' => 'required|numeric|min:0.1',
            'chambre_id' => 'required|exists:chambres_froides,id',

        ]);

        // Vérifier la capacité de la chambre
        $chambre = ChambreFroide::find($validated['chambre_id']);
        $poidsActuel = $chambre->lots()->where('statut', 'en_stock')->sum('poids_entree');
        $poidsRestant = $chambre->capacite - $poidsActuel;

        if ($validated['poids_entree'] > $poidsRestant) {
            return redirect()->back()
                ->with('error', 'Capacité insuffisante. Restant : ' . number_format($poidsRestant, 2) . ' kg');
        }

        $produit = Produit::find($validated['produit_id']);

        $lot = Lot::create([
            'client_id' => $validated['client_id'],
            'espece' => $produit->nom,
            'categorie' => $produit->categorie,
            'poids_entree' => $validated['poids_entree'],
            'poids_sortie' => $validated['poids_entree'],
            'chambre_id' => $validated['chambre_id'],
            'statut' => 'en_stock',
            'date_entree_chambre' => now(),
        ]);

        return redirect()->route('stock.index')
            ->with('success', 'Lot ' . $lot->numero . ' ajouté avec succès.');
    }
}
