<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use App\Models\ClientProduit;
use App\Models\Produit;
use App\Models\Tunnel;
use App\Models\ChambreFroide;
use App\Models\Tarif;
use App\Models\SortieChambre;
use App\Models\SortieTunnel;
use App\Models\Archive;
use App\Services\DocumentsArchives;
use App\Services\Sorties;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LotWebController extends Controller
{
    private function tarifTunnel(): float
    {
        return (float) (Tarif::where('designation', 'Tunnel')->value('montant') ?? 450);
    }

    private function tarifChambre(): float
    {
        return (float) (Tarif::where('designation', 'Chambre')->value('montant') ?? 25);
    }

    // Liste des entrées : 10 lots par page
    public function index()
    {
        $lots = Lot::with('client')
            ->where('statut', '!=', 'sorti')
            ->orderByDesc('id')
            ->paginate(10);

        return view('lots.index', compact('lots'));
    }

    // Afficher le formulaire
    public function create(Request $request)
    {
        $clients  = Client::orderBy('nom')->get();
        $produits = Produit::all();
        $tunnels  = Tunnel::all();
        $chambres = ChambreFroide::all();
        $lots     = Lot::with(['client', 'produit'])->where('statut', '!=', 'sorti')->orderBy('id', 'desc')->get();

        // Produits de chaque client : [client_id => ['Crevette royale', ...]]
        $produitsParClient = ClientProduit::orderBy('nom')->get()
            ->groupBy('client_id')
            ->map(fn($liste) => $liste->pluck('nom')->values());

        $tunnelSelectionne  = $request->query('tunnel_id');
        $chambreSelectionne = $request->query('chambre_id');

        $tarifTunnel  = $this->tarifTunnel();
        $tarifChambre = $this->tarifChambre();

        return view('lots.create', compact(
            'clients', 'produits', 'tunnels', 'chambres',
            'lots', 'tunnelSelectionne', 'chambreSelectionne',
            'tarifTunnel', 'tarifChambre', 'produitsParClient'
        ));
    }

    // Enregistrer un nouveau lot
    public function store(Request $request)
    {
        $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'espece'       => 'required|string|max:50',
            'poids_entree' => 'required|numeric|min:0.01',
            'origine'      => 'nullable|string|max:100',
            'tunnel_id'    => 'nullable|required_without:chambre_id|exists:tunnels,id',
            'chambre_id'   => 'nullable|required_without:tunnel_id|exists:chambre_froides,id',
        ]);

        $client = Client::findOrFail($request->client_id);
        $espece = trim($request->espece);

        $dernierLot     = Lot::orderBy('id', 'desc')->first();
        $prochainNumero = $dernierLot ? $dernierLot->id + 1 : 1;
        $numeroLot      = 'LOT-' . str_pad($prochainNumero, 4, '0', STR_PAD_LEFT);

        $lot               = new Lot();
        $lot->client_id    = $client->id;
        $lot->espece       = $espece;
        $lot->contact      = $client->contact;
        $lot->poids_entree = $request->poids_entree;
        $lot->tunnel_id    = $request->tunnel_id;
        $lot->chambre_id   = $request->chambre_id;
        $lot->statut       = $request->chambre_id ? 'en_stock' : 'en_congelation';

        if ($request->tunnel_id) {
            $lot->tarif_applique = $this->tarifTunnel();
            $lot->montant_tunnel = $request->poids_entree * $lot->tarif_applique;
        }

        if ($request->chambre_id) {
            $lot->date_entree_chambre = now();
            $lot->poids_restant       = $request->poids_entree;
            $lot->tarif_applique      = $this->tarifChambre();
        }
        $lot->save();

        // Le produit est ajouté à la fiche du client s'il n'y est pas déjà
        ClientProduit::firstOrCreate(['client_id' => $client->id, 'nom' => $espece]);

        // Retour au tableau de suivi des arrivées
        return redirect()->route('lots.index')
            ->with('success', 'Lot ' . $numeroLot . ' enregistré avec succès !');
    }

    public function printBon($id)
    {
        $lot = Lot::with(['client', 'tunnel', 'chambre'])->findOrFail($id);

        // lot saisi avec d'autres produits dans la même entrée : un seul bon pour toute la livraison
        $lots = $lot->entree_ref
            ? Lot::with(['client', 'tunnel', 'chambre'])->where('entree_ref', $lot->entree_ref)->orderBy('id')->get()
            : collect([$lot]);

        return $this->bonEntreeDe($lots, $lot->entree_ref ? 'entree:' . $lot->entree_ref : 'entree:lot' . $lot->id);
    }

    // Un seul bon d'entrée pour tous les produits d'une livraison : /lots/print-multiple?ids=12,13,14
    public function printBons(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)->filter()->unique()->values();

        $lots = Lot::with(['client', 'tunnel', 'chambre'])->whereIn('id', $ids)->orderBy('id')->get();
        abort_if($lots->isEmpty(), 404);
        abort_if($lots->pluck('client_id')->unique()->count() > 1, 422, 'Les lots doivent appartenir au même client.');

        $refs = $lots->pluck('entree_ref')->unique();
        $cle = $refs->count() === 1 && $refs->first() && Lot::where('entree_ref', $refs->first())->count() === $lots->count()
            ? 'entree:' . $refs->first()
            : 'entree:ids' . $lots->pluck('id')->implode(',');

        return $this->bonEntreeDe($lots, $cle);
    }

    /** Bon d'entrée : archivé à la première émission, réaffiché à l'identique ensuite. */
    private function bonEntreeDe($lots, string $cle)
    {
        [$archive, $duplicata] = DocumentsArchives::bonEntree($lots, $cle);

        return view('lots.print_multiple', $this->donneesDocument($archive, $duplicata));
    }

    /** Données communes à tous les documents archivés. */
    private function donneesDocument(Archive $archive, bool $duplicata, array $en_plus = []): array
    {
        return array_merge($archive->contenu, [
            'client' => (object) ['nom' => $archive->client_nom],
            'numero' => $archive->numero,
            'dateEmission' => $archive->created_at,
            'duplicata' => $duplicata,
            'societeSnap' => $archive->contenu['societe'] ?? null,
            'emisPar' => $archive->emis_par,
        ], $en_plus);
    }

    /** Affiche une archive telle qu'elle a été émise (page « Archives »). */
    public function voirArchive($id)
    {
        $archive = Archive::findOrFail($id);
        $archive->increment('impressions');

        $vues = ['bon_entree' => 'lots.print_multiple', 'bon_sortie' => 'lots.bon_sortie_groupe', 'facture' => 'lots.facture_groupee'];

        return view($vues[$archive->type], $this->donneesDocument($archive, true));
    }

    // Une seule facture pour plusieurs sorties d'un même client : /factures/groupee?chambre=1,2&tunnel=3
    public function factureGroupee(Request $request)
    {
        [$chambre, $tunnel] = $this->sortiesDemandees($request);

        return $this->factureDe($chambre, $tunnel);
    }

    // Un seul bon de sortie pour plusieurs sorties d'un même client : /sorties/bon-groupe?chambre=1,2&tunnel=3
    public function bonSortieGroupe(Request $request)
    {
        [$chambre, $tunnel] = $this->sortiesDemandees($request);

        return $this->bonSortieDe($chambre, $tunnel, false);
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection} */
    private function sortiesDemandees(Request $request): array
    {
        $ids = fn (string $cle) => collect(explode(',', (string) $request->query($cle)))
            ->map(fn ($id) => (int) $id)->filter()->unique()->values();

        $chambre = SortieChambre::with('lot.client')->whereIn('id', $ids('chambre'))->get();
        $tunnel = SortieTunnel::with('lot.client')->whereIn('id', $ids('tunnel'))->get();

        abort_if($chambre->isEmpty() && $tunnel->isEmpty(), 404);
        abort_if($chambre->merge($tunnel)->map(fn ($x) => $x->lot->client_id)->unique()->count() > 1, 422, 'Les documents doivent appartenir au même client.');

        return [$chambre, $tunnel];
    }

    private function factureDe($chambre, $tunnel)
    {
        [$archive, $duplicata] = DocumentsArchives::facture($chambre, $tunnel);

        return view('lots.facture_groupee', $this->donneesDocument($archive, $duplicata));
    }

    private function bonSortieDe($chambre, $tunnel, bool $imprimerAuto)
    {
        $resultat = DocumentsArchives::bonSortie($chambre, $tunnel);

        // la preuve n'existe qu'une fois la sortie physique effectuée
        if (! $resultat) {
            return view('lots.bon_sortie_groupe', [
                'client' => $chambre->merge($tunnel)->first()->lot->client,
                'lignes' => DocumentsArchives::lignes($chambre, $tunnel)->all(),
                'imprimerAuto' => $imprimerAuto,
            ]);
        }

        [$archive, $duplicata] = $resultat;

        return view('lots.bon_sortie_groupe', $this->donneesDocument($archive, $duplicata, ['imprimerAuto' => $imprimerAuto && ! $duplicata]));
    }

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

        $lot->montant_chambre = $lot->poids_sortie * $this->tarifChambre();

        $lot->save();

        return redirect()->back()
            ->with('success', 'Contre-pesée enregistrée pour le lot ' . $lot->numero);
    }

    // ÉTAPE 1 — Le magasinier demande une sortie tunnel : calcule et facture
    public function demanderSortieTunnel(Request $request, $id)
    {
        $lot = Lot::with(['client', 'tunnel'])->findOrFail($id);

        if (!$lot->tunnel_id || $lot->statut !== 'en_congelation') {
            return redirect()->back()->with('error', 'Ce lot n\'est plus en tunnel.');
        }

        if ($message = Sorties::tunnelFerme($lot)) {
            return redirect()->back()->with('error', $message);
        }

        $sortie = Sorties::sortirTunnel($lot, $request->filled('poids') ? (float) $request->query('poids') : null);

        return redirect()->route('sorties.bonGroupe', ['tunnel' => $sortie->id])
            ->with('success', 'Bon de sortie créé pour ' . $lot->numero . ' — le client le porte à la caisse.');
    }

    // ÉTAPE 2 — La caisse encaisse et marque la facture payée
    public function payerSortieTunnel($id)
    {
        $sortie = SortieTunnel::findOrFail($id);

        if ($sortie->statut !== 'en_attente_paiement') {
            return redirect()->back()->with('error', 'Cette facture n\'est plus en attente de paiement.');
        }

        $sortie->statut = 'paye';
        $sortie->save();

        return redirect()->route('factures.index')
            ->with('success', 'Facture marquée comme payée. Le client peut retourner voir le magasinier.');
    }

    // ÉTAPE 3 — Le magasinier confirme la sortie physique une fois la facture payée
    public function confirmerSortieTunnel($sortieId)
    {
        $sortie = SortieTunnel::with('lot.client')->findOrFail($sortieId);

        if ($sortie->statut !== 'paye') {
            return redirect()->back()->with('error', 'La facture doit être payée avant la sortie physique.');
        }

        Sorties::confirmerTunnel($sortie);

        return redirect()->back()->with('success', 'Sortie confirmée : les produits sont remis au client.');
    }

    // Affiche le formulaire de sortie partielle
    public function formSortieChambre($id)
    {
        $lot = Lot::with(['client', 'chambre'])->findOrFail($id);

        if (!$lot->chambre_id || $lot->statut !== 'en_stock') {
            return redirect()->back()->with('error', 'Ce lot n\'est plus en chambre.');
        }

        return view('lots.form_sortie_chambre', compact('lot'));
    }

    // ÉTAPE 1 — Le magasinier demande une sortie : calcule et facture, sans sortir physiquement
    public function demanderSortieChambre(Request $request, $id)
    {
        $lot = Lot::with(['client', 'chambre'])->findOrFail($id);

        if (!$lot->chambre_id || $lot->statut !== 'en_stock') {
            return redirect()->back()->with('error', 'Ce lot n\'est plus en chambre.');
        }

        $poidsDisponible = $lot->poids_restant ?? $lot->poids_entree;

        $request->validate([
            'poids_sortie' => 'required|numeric|min:0.01|max:' . $poidsDisponible,
        ]);

        $poidsSortie = (float) $request->poids_sortie;

        $sortie = Sorties::sortirChambre($lot, $poidsSortie);
        $montant = $sortie->montant;

        return redirect()->route('sorties.bonGroupe', ['chambre' => $sortie->id])
            ->with('success', 'Bon de sortie créé pour ' . $lot->numero . ' — le client le porte à la caisse (' . number_format($montant, 0, ',', ' ') . ' Ar).');
    }

    // ÉTAPE 2 — La caisse encaisse et marque la facture payée
    public function payerSortieChambre($id)
    {
        $sortie = SortieChambre::findOrFail($id);

        if ($sortie->statut !== 'en_attente_paiement') {
            return redirect()->back()->with('error', 'Cette facture n\'est plus en attente de paiement.');
        }

        $sortie->statut = 'paye';
        $sortie->save();

        return redirect()->route('factures.index')
            ->with('success', 'Facture marquée comme payée. Le client peut retourner voir le magasinier.');
    }

    // ÉTAPE 3 — Le magasinier confirme la sortie physique une fois la facture payée
    public function confirmerSortieChambre($sortieId)
    {
        $sortie = SortieChambre::with('lot.client')->findOrFail($sortieId);

        if ($sortie->statut !== 'paye') {
            return redirect()->back()->with('error', 'La facture doit être payée avant la sortie physique.');
        }

        Sorties::confirmerChambre($sortie);

        return redirect()->back()->with('success', 'Sortie confirmée : les produits sont remis au client.');
    }

    // Facture de sortie tunnel
    public function factureTunnel($id)
    {
        $sortie = SortieTunnel::with('lot.client')->findOrFail($id);

        return $this->factureDe(collect(), collect([$sortie]));
    }

    // Facture d'une sortie partielle de chambre
    public function factureSortieChambre($id)
    {
        $sortie = SortieChambre::with('lot.client')->findOrFail($id);

        return $this->factureDe(collect([$sortie]), collect());
    }

    // Supprime définitivement un lot
    public function destroy($id)
    {
        $lot    = Lot::findOrFail($id);
        $numero = $lot->numero;
        $lot->delete();

        return redirect()->back()
            ->with('success', 'Lot ' . $numero . ' supprimé avec succès !');
    }
}
