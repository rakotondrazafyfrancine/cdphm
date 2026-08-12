<?php

namespace App\Http\Controllers;

// Importation des modèles
use App\Models\ChambreFroide;
use App\Models\Lot;
use Illuminate\Http\Request;

class ChambreWebController extends Controller
{
    /**
     * Affiche la liste de toutes les chambres froides
     * Page : /chambres
     */
    public function index()
    {
        // Récupère toutes les chambres
        $chambres = ChambreFroide::all();

        // Retourne la vue avec la liste des chambres
        return view('chambres.index', compact('chambres'));
    }

    /**
     * Affiche les détails d'une chambre spécifique avec ses lots
     * Page : /chambres/{id}
     */
public function show($id)
{
    // Récupère la chambre
    $chambre = ChambreFroide::findOrFail($id);

    // Récupère tous les lots dans cette chambre
    $lots = Lot::where('chambre_id', $id)
        ->where('statut', '!=', 'sorti')
        ->with(['client'])
        ->get();

    // Récupère les lots encore en tunnel, pas encore transférés en chambre
    $lotsDisponibles = Lot::whereNull('chambre_id')
        ->where('statut', 'en_stock')
        ->with(['client'])
        ->get();

    // Calculs d'occupation
    $poidsActuel = $lots->sum('poids_entree');
    $capaciteKg = $chambre->capacite ?? 0;
    $taux = $capaciteKg > 0 ? round(($poidsActuel / $capaciteKg) * 100, 1) : 0;

    // Retourne la vue avec les données
    return view('chambres.show', compact('chambre', 'lots', 'lotsDisponibles', 'poidsActuel', 'capaciteKg', 'taux'));
}
    public function store(Request $request)
{
    $validated = $request->validate([
        'client_id' => 'required|exists:clients,id',
        'contact' => 'nullable|string|max:50',
        'espece' => 'required|string|max:255',
        'nb_filets' => 'nullable|integer|min:0',
        'nb_poissons' => 'nullable|integer|min:0',
        'nb_bacs' => 'nullable|integer|min:0',
        'poids_entree' => 'required|numeric|min:0.1',
        'tunnel_id' => 'nullable|exists:tunnels,id',
        'dockers' => 'nullable|string|max:255',
        'heures_tunnel' => 'nullable|numeric|min:0',
    ]);

    $lot = Lot::create([
        'client_id' => $validated['client_id'],
        'contact' => $validated['contact'] ?? null,
        'espece' => $validated['espece'],
        'categorie' => null,
        'nb_filets' => $validated['nb_filets'] ?? 0,
        'nb_poissons' => $validated['nb_poissons'] ?? 0,
        'nb_bacs' => $validated['nb_bacs'] ?? 0,
        'poids_entree' => $validated['poids_entree'],
        'poids_sortie' => $validated['poids_entree'],
        'tunnel_id' => $validated['tunnel_id'] ?? null,
        'equipe manutention' => $validated['dockers'] ?? null,
        'heures_tunnel' => $validated['heures_tunnel'] ?? 24,
        'statut' => 'en_stock', // ou 'en_stock' selon votre logique
        'date_entree_chambre' => null,
    ]);

    return redirect()->route('entrees.index')
        ->with('success', 'Lot ' . $lot->numero . ' enregistré.');
}
public function assignerLot(Request $request, $id)
{
    $validated = $request->validate([
        'lot_id' => 'required|exists:lots,id',
    ]);

    $chambre = ChambreFroide::findOrFail($id);
    $lot = Lot::findOrFail($validated['lot_id']);

    // Vérifier la capacité
    $poidsActuel = $chambre->lots()->where('statut', 'en_stock')->sum('poids_entree');
    if (($poidsActuel + $lot->poids_entree) > $chambre->capacite_tonnes * 1000) {
        return redirect()->route('chambres.show', $chambre->id)
            ->with('error', 'Capacité insuffisante.');
    }

    $lot->chambre_id = $chambre->id;
    $lot->statut = 'en_stock';
    $lot->date_entree_chambre = now();
    $lot->save();

    return redirect()->route('chambres.show', $chambre->id)
        ->with('success', 'Lot assigné à la chambre.');
}
}
