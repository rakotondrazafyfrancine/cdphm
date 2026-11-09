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
