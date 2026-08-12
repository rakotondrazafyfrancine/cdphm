<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceStockage;
use Illuminate\Http\Request;

class ServiceStockageController extends Controller
{
    // Lister tous les services de stockage
    public function index()
    {
        return response()->json(
            ServiceStockage::with(['lot', 'chambreFroide'])->get()
        );
    }

    // Creer un service de stockage (transfert vers chambre froide)
    public function store(Request $request)
    {
        $request->validate([
            'lot_id'           => 'required|exists:lots,id',
            'chambre_froide_id'=> 'required|exists:chambre_froides,id',
            'date_entree'      => 'required|date',
            'duree_indeterminee' => 'nullable|boolean',
        ]);

        $service = ServiceStockage::create($request->all());

        return response()->json(
            $service->load(['lot', 'chambreFroide']), 201
        );
    }

    // Calculer le cout chambre froide
    public function calculerCout(ServiceStockage $service)
    {
        $tarifChambre = 25; // Ar/kg/jour

        // Si duree indeterminee, calcule selon temps reel passe
        if ($service->duree_indeterminee) {
            $jours = now()->diffInDays($service->date_entree);
            $jours = max($jours, 1/24); // minimum quelques heures
        } else {
            // Sinon, duree entre date_entree et date_sortie (ou maintenant)
            $dateSortie = $service->date_sortie ?? now();
            $jours = \Carbon\Carbon::parse($service->date_entree)
                                   ->diffInDays($dateSortie);
            $jours = max($jours, 1/24);
        }

        $montant = round(
            $service->lot->poids_sortie * $jours * $tarifChambre
        );

        return response()->json([
            'jours'     => round($jours, 2),
            'tarif'     => $tarifChambre,
            'poids'     => $service->lot->poids_sortie,
            'montant'   => $montant
        ]);
    }

    // Afficher un service precis
    public function show(ServiceStockage $serviceStockage)
    {
        return response()->json(
            $serviceStockage->load(['lot', 'chambreFroide'])
        );
    }

    // Modifier
    public function update(Request $request,
                           ServiceStockage $serviceStockage)
    {
        $serviceStockage->update($request->all());

        return response()->json(
            $serviceStockage->load(['lot', 'chambreFroide'])
        );
    }

    // Supprimer
    public function destroy(ServiceStockage $serviceStockage)
    {
        $serviceStockage->delete();

        return response()->json([
            'message' => 'Service de stockage supprime'
        ]);
    }
}
