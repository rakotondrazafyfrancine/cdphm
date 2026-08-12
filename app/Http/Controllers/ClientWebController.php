<?php

namespace App\Http\Controllers;

// Importation du modèle Client
use App\Models\Client;
use Illuminate\Http\Request;

class ClientWebController extends Controller
{
    /**
     * Affiche la liste de tous les clients
     * Page : /clients
     */
    public function index()
    {
        // Récupère tous les clients
        $clients = Client::all();

        // Retourne la vue avec la liste des clients
        return view('clients.index', compact('clients'));
    }

    /**
     * Affiche le formulaire de création d'un client
     * Page : /clients/create
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Enregistre un nouveau client dans la base de données
     * Page : POST /clients
     */
    public function store(Request $request)
    {
        // Validation : le nom est obligatoire
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        // Création du client
        Client::create($request->all());

        // Redirige vers la liste avec un message de succès
        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès !');
    }

    /**
     * Affiche le formulaire de modification d'un client
     * Page : /clients/{id}/edit
     */
    public function edit($id)
    {
        // Récupère le client à modifier
        $client = Client::findOrFail($id);

        return view('clients.edit', compact('client'));
    }

    /**
     * Met à jour les informations d'un client
     * Page : PUT /clients/{id}
     */
    public function update(Request $request, $id)
    {
        // Récupère le client
        $client = Client::findOrFail($id);

        // Met à jour les informations
        $client->update($request->all());

        // Redirige vers la liste avec un message de succès
        return redirect()->route('clients.index')
            ->with('success', 'Client modifié avec succès !');
    }

    /**
     * Supprime un client de la base de données
     * Page : DELETE /clients/{id}
     */
    public function destroy($id)
    {
        // Supprime le client
        Client::destroy($id);

        // Redirige vers la liste avec un message de succès
        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès !');
    }
}
