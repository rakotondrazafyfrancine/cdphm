<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lot;
use App\Models\SortieChambre;
use App\Models\SortieTunnel;
use Illuminate\Http\Request;

class ClientWebController extends Controller
{
    private function regles(?int $id = null): array
    {
        return [
            'nom'     => 'required|string|max:100|unique:clients,nom' . ($id ? ',' . $id : ''),
            'contact' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
            'type'    => 'nullable|string|max:50',
        ];
    }

    public function index()
    {
        $clients = Client::with('produits')->orderBy('nom')->get();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        Client::create($request->validate($this->regles()));

        return redirect()->route('clients.index')->with('success', 'Client enregistré avec succès.');
    }

    // Historique d'un client : entrées et sorties
    public function show($id)
    {
        $client = Client::with('produits')->findOrFail($id);
        $lots   = Lot::where('client_id', $client->id)->orderByDesc('id')->get();

        $deClient = fn($q) => $q->where('client_id', $client->id);

        $sorties = SortieChambre::with('lot')->whereHas('lot', $deClient)->get()
            ->map(fn($s) => (object) ['type' => 'Chambre', 'lot' => $s->lot, 'poids' => $s->poids_sorti, 'date' => $s->created_at, 'montant' => $s->montant, 'statut' => $s->statut])
            ->concat(
                SortieTunnel::with('lot')->whereHas('lot', $deClient)->get()
                    ->map(fn($s) => (object) ['type' => 'Tunnel', 'lot' => $s->lot, 'poids' => $s->poids_sorti, 'date' => $s->created_at, 'montant' => $s->montant, 'statut' => $s->statut])
            )
            ->sortByDesc('date');

        return view('clients.show', compact('client', 'lots', 'sorties'));
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);

        return view('clients.create', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->update($request->validate($this->regles($client->id)));

        return redirect()->route('clients.index')->with('success', 'Client modifié avec succès.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        if ($client->lots()->exists()) {
            return redirect()->route('clients.index')
                ->with('error', 'Ce client a des lots enregistrés : il ne peut pas être supprimé.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }
}
