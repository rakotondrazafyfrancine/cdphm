<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use App\Models\Produit;
use App\Models\Tunnel;
use App\Models\ChambreFroide;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LotWebController extends Controller
{
    // Afficher le formulaire + la liste des lots
    public function create(Request $request)
    {
        $clients  = Client::all();
        $produits = Produit::all();
        $tunnels  = Tunnel::all();
        $chambres = ChambreFroide::all();
        $lots     = Lot::with(['client', 'produit'])->where('statut', '!=', 'sorti')->orderBy('id', 'desc')->get();

        // Récupère le tunnel_id ou chambre_id envoyé dans l'URL
        // pour pré-sélectionner automatiquement dans le formulaire
        $tunnelSelectionne  = $request->query('tunnel_id');
        $chambreSelectionne = $request->query('chambre_id');

        return view('lots.create', compact(
            'clients', 'produits', 'tunnels', 'chambres',
            'lots', 'tunnelSelectionne', 'chambreSelectionne'
        ));
    }

    // Enregistrer un nouveau lot
    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'espece'             => 'required|string|max:50',
            'poids_entree'       => 'required|numeric|min:0.01',
            'origine'            => 'nullable|string|max:100',
            'nb_filets'          => 'nullable|integer|min:0',
            'nb_poissons'        => 'nullable|integer|min:0',
            'nb_bacs'            => 'nullable|integer|min:0',
            'equipe_manutention' => 'nullable|string|max:255',
            'tunnel_id'          => 'nullable|required_without:chambre_id|exists:tunnels,id',
            'chambre_id'         => 'nullable|required_without:tunnel_id|exists:chambre_froides,id',
        ]);

        // Génère un numéro de lot unique (LOT-0001, LOT-0002...)
        $dernierLot      = Lot::orderBy('id', 'desc')->first();
        $prochainNumero  = $dernierLot ? $dernierLot->id + 1 : 1;
        $numeroLot       = 'LOT-' . str_pad($prochainNumero, 4, '0', STR_PAD_LEFT);

        // Crée le lot
        $lot                  = new Lot();
        $lot->client_id       = $request->client_id;
        $lot->espece          = $request->espece;
        $lot->contact         = $request->contact;
        $lot->poids_entree    = $request->poids_entree;// même valeur au départ
        $lot->nb_filets       = $request->nb_filets ?? 0;
        $lot->nb_poissons     = $request->nb_poissons ?? 0;
        $lot->nb_bacs         = $request->nb_bacs ?? 0;
        $lot->equipe_manutention = $request->equipe_manutention;
        $lot->tunnel_id       = $request->tunnel_id;
        $lot->chambre_id      = $request->chambre_id;
        $lot->statut           = $request->chambre_id ? 'en_stock' : 'en_congelation';


// Calcul du montant tunnel (450 Ar/kg, forfait sur poids brut) si le lot va en tunnel
if ($request->tunnel_id) {
    $lot->montant_tunnel = $request->poids_entree * 450;
}

// Date d'entree en chambre si le lot va directement en chambre
if ($request->chambre_id) {
    $lot->date_entree_chambre = now();
}

        $lot->save();

        // Redirige selon d'où on vient
        if ($request->tunnel_id) {
            return redirect()->route('tunnels.show', $request->tunnel_id)
                ->with('success', 'Lot ' . $numeroLot . ' enregistré avec succès !');
        }

        if ($request->chambre_id) {
            return redirect()->route('chambres.show', $request->chambre_id)
                ->with('success', 'Lot ' . $numeroLot . ' enregistré avec succès !');
        }

        return redirect()->route('lots.create')
            ->with('success', 'Lot ' . $numeroLot . ' enregistré avec succès !');
    }

    public function printBon($id)
{
    $lot = Lot::with(['client', 'tunnel', 'chambre'])->findOrFail($id);

    // Détecter l'infrastructure réelle
    if ($lot->chambre_id) {
        $type = 'chambre';
        $tarif = 25; // Tarif chambre (Ar/kg/jour)
        $infrastructure = $lot->chambre;
        $dureeLabel = 'Stockage en chambre';
        $heures = 24; // pour l'affichage, on prend une journée type
        $duree = 1;   // en jours
    } else {
        $type = 'tunnel';
        $tarif = 450; // Tarif tunnel
        $infrastructure = $lot->tunnel;
        $dureeLabel = 'Congélation en tunnel';
        $heures = $lot->heures_tunnel ?? 24;
        $duree = max($heures / 24, 1 / 24);
    }

    // Calcul du montant estimé
    $montantEstime = $lot->poids_entree * $duree * $tarif;

    return view('lots.print', compact(
        'lot',
        'type',
        'tarif',
        'infrastructure',
        'dureeLabel',
        'heures',
        'duree',
        'montantEstime'
    ));
}

    // Met à jour la contre-pesée d'un lot en chambre froide
   public function majContrePesee(Request $request, $id)
{
    $request->validate([
        'contre_pesee' => 'required|numeric|min:0',
    ]);

    $lot = Lot::findOrFail($id);
    $lot->poids_sortie  = $request->contre_pesee;
    $lot->ecart_freinte = $lot->poids_entree > 0
        ? round((($lot->poids_sortie - $lot->poids_entree) / $lot->poids_entree) * 100, 2)
        : 0;

    // Calcul du montant chambre (25 Ar/kg, sur le poids de contre-pesée)
    $lot->montant_chambre = $lot->poids_sortie * 25;

    $lot->save();

    return redirect()->back()
        ->with('success', 'Contre-pesée enregistrée pour le lot ' . $lot->numero);
}
// Sortie définitive d'un lot depuis le TUNNEL
public function sortirTunnel($id)
{
    $lot = Lot::with(['client', 'tunnel'])->findOrFail($id);

    if (!$lot->tunnel_id || $lot->statut !== 'en_congelation') {
        return redirect()->back()->with('error', 'Ce lot n\'est plus en tunnel.');
    }

    $heures = $lot->created_at ? now()->diffInHours($lot->created_at, absolute: true) : 0;
    $jours = max(ceil($heures / 24), 1);
    $tarif = 450;
    $montant = $lot->poids_entree * $jours * $tarif;

    $lot->statut = 'sorti';
    $lot->date_sortie = now();
    $lot->tunnel_id = null;
    $lot->save();

    return view('lots.print_bon_sortie_tunnel', compact('lot', 'heures', 'jours', 'tarif', 'montant'));
}

// Sortie définitive d'un lot depuis la CHAMBRE
public function sortirChambre($id)
{
    $lot = Lot::with(['client', 'chambre'])->findOrFail($id);

    if (!$lot->chambre_id || $lot->statut !== 'en_stock') {
        return redirect()->back()->with('error', 'Ce lot n\'est plus en chambre.');
    }

    $lot->date_sortie = now();

    $jours = 0;
    if ($lot->date_entree_chambre) {
        $jours = Carbon::parse($lot->date_entree_chambre)->diffInDays($lot->date_sortie, absolute: true);
        $jours = max((int) ceil($jours), 1);
    }

    $tarifChambre = 25;
    $montant = $lot->poids_entree * $jours * $tarifChambre;

    $lot->statut = 'sorti';
    $lot->chambre_id = null;
    $lot->date_entree_chambre = null;
    $lot->save();

    return view('lots.bon_sortie', compact('lot', 'jours', 'tarifChambre', 'montant'));
}
    // Affiche la facture de sortie chambre froide
    public function factureChambre($id)
    {
        $lot = Lot::with(['client', 'produit'])->findOrFail($id);
        return view('lots.facture_chambre', compact('lot'));
    }

    // Supprime définitivement un lot
    public function destroy($id)
    {
        $lot    = Lot::findOrFail($id);
        $numero = $lot->numero_lot;
        $lot->delete();

        return redirect()->back()
            ->with('success', 'Lot ' . $numero . ' supprimé avec succès !');
    }

    // Assigne un lot existant à un tunnel
    public function assignerTunnel(Request $request, $tunnelId)
    {
        $request->validate([
            'lot_id' => 'required|exists:lots,id',
        ]);

        $lot            = Lot::findOrFail($request->lot_id);
        $lot->tunnel_id = $tunnelId;
        $lot->save();

        return redirect()->route('tunnels.show', $tunnelId)
            ->with('success', 'Lot ' . $lot->numero_lot . ' assigné au tunnel avec succès !');
    }
    /**
 * Assigne un lot existant (en tunnel) à une chambre froide
 */
public function assignerChambre(Request $request, $id)
{
    $request->validate([
        'chambre_id' => 'required|exists:chambres_froides,id',
    ]);

    $lot = Lot::findOrFail($id);

    $lot->update([
        'chambre_id' => $request->chambre_id,
        'type_infrastructure' => 'chambre',
        'date_entree_chambre' => now(),
    ]);

    return redirect()->route('chambres.show', $request->chambre_id)
        ->with('success', '✅ Lot assigné à la chambre avec succès.');
}
}
