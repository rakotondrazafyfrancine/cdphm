<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use Illuminate\Http\Request;

class LotController extends Controller
{
    // Lister tous les lots avec client et produit
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Lot::with(['client', 'produit'])->get()
        ]);
    }

    // Créer un nouveau lot (réception quai)
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'produit_id' => 'required|exists:produits,id',
            'poids_entree' => 'required|numeric|min:0',
            'date_entree' => 'required|date',
            'contact' => 'nullable|string|max:50',
            'origine' => 'nullable|string|max:100',
            'nb_filets' => 'nullable|integer|min:0',
            'nb_poissons' => 'nullable|integer|min:0',
            'nb_bacs' => 'nullable|integer|min:0',
            'equipe_manutention' => 'nullable|string|max:255',
        ]);

        // Générer un numéro de lot unique
        $dernierLot = Lot::orderBy('id', 'desc')->first();
        $prochainNumero = $dernierLot ? $dernierLot->id + 1 : 1;
        $numeroLot = 'LOT-' . str_pad($prochainNumero, 4, '0', STR_PAD_LEFT);

        // Créer le lot
        $lot = new Lot();
        $lot->numero_lot = $numeroLot;
        $lot->client_id = $request->client_id;
        $lot->produit_id = $request->produit_id;
        $lot->poids_entree = $request->poids_entree;
        $lot->date_entree = $request->date_entree;
        $lot->contact = $request->contact;
        $lot->origine = $request->origine;
        $lot->nb_filets = $request->nb_filets ?? 0;
        $lot->nb_poissons = $request->nb_poissons ?? 0;
        $lot->nb_bacs = $request->nb_bacs ?? 0;
        $lot->equipe_manutention = $request->equipe_manutention;
        $lot->statut = 'en_attente';

        // Redirection vers le formulaire

        return redirect()->route('lots.create')->with('success', 'Lot' . $numeroLot .'cree avec succes !');
    }

    // Afficher un lot précis
    public function show(Lot $lot)
    {
        return response()->json([
            $lot->load(['client', 'produit', 'serviceCongelation.tunnel', 'serviceStockages.chambreFroide'])
        ]);
    }

    // Modifier un lot + calcul automatique écart freinte
    public function update(Request $request, Lot $lot)
    {
        // Calcul automatique de l'écart freinte si poids sortie
        if ($request->has('poids_sortie') && $lot->poids_entree > 0) {
            $ecart = ($lot->poids_entree - $request->poids_sortie) / $lot->poids_entree * 100;
            $request->merge(['ecart_freinte' => round($ecart, 2)]);
        }

        $lot->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lot mis a jour',
            $lot->load(['client', 'produit'])
        ]);
    }

    // Libérer un lot (sortie physique)
    public function liberer(Lot $lot)
    {
        $lot->update([
            'statut' => 'sorti',
            'date_sortie' => now(),
            'etape' => 'sortie'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lot libéré avec succès',
            'data' => $lot
        ]);
    }

    // Supprimer un lot
    public function destroy(Lot $lot)
    {
        $lot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lot supprime avec succès',
        ]);
    }
}
