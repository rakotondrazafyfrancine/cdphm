<?php

namespace App\Http\Controllers;

use App\Models\Tunnel;
use App\Models\Lot;
use Illuminate\Http\Request;

class TunnelWebController extends Controller
{
    /**
     * Affiche la liste des tunnels avec leur occupation et la liste des lots en tunnel.
     */
    public function index()
    {
        // Récupérer tous les tunnels avec leurs lots
        $tunnels = Tunnel::with('lots')->get();

        // Calculer le poids actuel pour chaque tunnel
        foreach ($tunnels as $tunnel) {
            $tunnel->poids_actuel = $tunnel->lots()->where('statut', 'en_congelation')->sum('poids_entree');
        }

        // Récupérer tous les lots en stock dans les tunnels (pour le tableau)
        $tousLesLots = Lot::whereNotNull('tunnel_id')
            ->where('statut', 'en_congelation')
            ->with(['client', 'tunnel'])
            ->latest()
            ->get();



        return view('tunnels.index', compact('tunnels', 'tousLesLots'));
    }

    /**
     * Affiche les détails d'un tunnel spécifique avec ses lots.
     */
    public function show($id)
    {
        $tunnel = Tunnel::findOrFail($id);

        // Recupere tous les lots dans le tunnel
        $lots = Lot::where('tunnel_id', $id)
            ->where('statut', 'en_congelation')
             ->with(['client'])
             ->orderBy('id', 'desc')
             ->get();

        // Calcul du poids actuel
        $poidsActuel = $lots->sum('poids_entree');
        $capaciteKg = $tunnel->capacite_tonnes*1000;
        $taux = $capaciteKg > 0 ? round(($poidsActuel/ $capaciteKg) * 100, 1) : 0;

        return view('tunnels.show', compact('tunnel', 'lots', 'poidsActuel', 'capaciteKg', 'taux'));
    }

    /**
     * Affiche le formulaire de modification d'un tunnel.
     */
    public function edit($id)
    {
        $tunnel = Tunnel::findOrFail($id);
        return view('tunnels.edit', compact('tunnel'));
    }

    /**
     * Met à jour un tunnel existant.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom'      => 'required|string|max:255',
            'capacite' => 'required|numeric|min:0',
            'statut'   => 'required|in:disponible,occupe,maintenance'
        ]);

        $tunnel = Tunnel::findOrFail($id);
        $tunnel->update($validated);

        return redirect()->route('tunnels.index')
            ->with('success', 'Tunnel mis à jour avec succès.');
    }

    /**
     * Supprime un tunnel.
     */
    public function destroy($id)
    {
        $tunnel = Tunnel::findOrFail($id);

        // Vérifier si le tunnel contient des lots en stock
        if ($tunnel->lots()->where('statut', 'en_stock')->exists()) {
            return redirect()->route('tunnels.index')
                ->with('error', 'Impossible de supprimer ce tunnel car il contient encore des lots en stock.');
        }

        $tunnel->delete();

        return redirect()->route('tunnels.index')
            ->with('success', 'Tunnel supprimé avec succès.');
    }

    /**
     * Génère un bon de sortie pour un tunnel (impression).
     */
    public function print($id)
    {
        $tunnel = Tunnel::with('lots.client')->findOrFail($id);
        return view('tunnels.print', compact('tunnel'));
    }
}
