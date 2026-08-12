<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitWebController extends Controller
{
    /**
     * Liste des produits
     */
    public function index()
    {
        $produits = Produit::latest()->get();
        return view('produits.index', compact('produits'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('produits.create');
    }

    /**
     * Enregistrer un nouveau produit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:produits,nom',
            'categorie' => 'required|in:poisson,crevette,crabe,langouste'
        ]);

        Produit::create($validated);

        return redirect()->route('produits.index')
            ->with('success', 'Produit ajouté avec succès.');
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $produit = Produit::findOrFail($id);
        return view('produits.edit', compact('produit'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:produits,nom,' . $id,
            'categorie' => 'required|in:poisson,crevette,crabe,langouste'
        ]);

        $produit->update($validated);

        return redirect()->route('produits.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        $produit = Produit::findOrFail($id);

        // Vérifier si le produit est utilisé dans des lots
        if ($produit->lots()->exists()) {
            return redirect()->route('produits.index')
                ->with('error', 'Impossible de supprimer ce produit car il est utilisé dans des lots.');
        }

        $produit->delete();

        return redirect()->route('produits.index')
            ->with('success', 'Produit supprimé avec succès.');
    }
}
