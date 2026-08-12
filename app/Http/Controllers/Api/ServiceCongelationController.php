<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCongelation;
use Illuminate\Http\Request;

class ServiceCongelationController extends Controller
{
    // Lister tous les services de congelation
    public function index()
    {
        return response()->json(
            ServiceCongelation::with(['lot', 'tunnel'])->get()
        );
    }

    // Creer un service de congelation (affecter lot a tunnel)
    public function store(Request $request)
    {
        $request->validate([
            'lot_id'    => 'required|exists:lots,id',
            'tunnel_id' => 'required|exists:tunnels,id',
            'date_debut'=> 'required|date',
        ]);

        $service = ServiceCongelation::create($request->all());

        return response()->json(
            $service->load(['lot', 'tunnel']), 201
        );
    }

    // Calculer le cout tunnel (avec penalite si depassement 24h)
    public function calculerCout(ServiceCongelation $service)
    {
        $tarifTunnel = 450; // Ar/kg/jour

        // Duree en heures depuis l'entree en tunnel
        $heures = now()->diffInHours($service->date_debut);

        // Conversion en jours (minimum 1 heure comptee)
        $jours = max($heures / 24, 1/24);

        // Montant de base (minimum 1 jour facture)
        $montant = round(
            $service->lot->poids_entree * max($jours, 1) * $tarifTunnel
        );

        // Penalite si depassement 24h et responsabilite client
        $penalite = 0;
        if ($heures > 24 &&
            $service->responsabilite_penalite === 'client') {
            $heuresExcedentaires = $heures - 24;
            $penalite = round(
                $service->lot->poids_sortie
                * ($heuresExcedentaires / 24)
                * $tarifTunnel * 1.5 // majoration 50% pour heures excedentaires
            );
            $montant = round(
                $service->lot->poids_sortie * (24/24) * $tarifTunnel
            ) + $penalite;
        }

        return response()->json([
            'heures'    => $heures,
            'jours'     => round($jours, 2),
            'montant'   => $montant,
            'penalite'  => $penalite,
            'cout_total'=> $montant,
            'tarif'     => $tarifTunnel
        ]);
    }

    // Afficher un service precis
    public function show(ServiceCongelation $serviceCongelation)
    {
        return response()->json(
            $serviceCongelation->load(['lot', 'tunnel'])
        );
    }

    // Modifier un service de congelation
    public function update(Request $request,
                           ServiceCongelation $serviceCongelation)
    {
        $serviceCongelation->update($request->all());

        return response()->json(
            $serviceCongelation->load(['lot', 'tunnel'])
        );
    }

    // Supprimer
    public function destroy(ServiceCongelation $serviceCongelation)
    {
        $serviceCongelation->delete();

        return response()->json([
            'message' => 'Service de congelation supprime'
        ]);
    }
}
