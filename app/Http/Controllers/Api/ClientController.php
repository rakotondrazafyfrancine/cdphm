<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //Lister tout les clients
    public function index()
    {
        return response()->json(Client::all());

    }

    //Creer un nouveau client
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'contact' => 'nullable|string',
            'adresse' => 'nullable|string',
            'type' => 'required|in:collecteur,societe_peche',

        ]);

        $client = Client::create($request->all());

        return response()->json($client, 201);
    }

    //Afficher un client precis
    public function show(Client $client)
    {
        return response()->json($client);
    }


     //Modifier un client
    public function update(Request $request, Client $client)
    {
        $client->update($request->all());

        return response()->json($client);
    }

    //Supprimer un client
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(['message' => 'Client supprime']);
    }
}
