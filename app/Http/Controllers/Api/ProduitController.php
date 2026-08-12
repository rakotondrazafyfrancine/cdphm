<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    // Lister tous les produits
    public function index()
    {
        return response()->json(Produit::all());
    }

    // Creer un nouveau produit
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'categorie' => 'nullable|string',
        ]);

        $produit = Produit::create($request->all());

        return response()->json($produit, 201);
    }

    // Afficher un produit precis
    public function show(Produit $produit)
    {
        return response()->json($produit);
    }

    // Modifier un produit
    public function update(Request $request, Produit $produit)
    {
        $produit->update($request->all());

        return response()->json($produit);
    }

    // Supprimer un produit
    public function destroy(Produit $produit)
    {
        $produit->delete();

        return response()->json(['message' => 'Produit supprime']);
    }
}
