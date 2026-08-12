<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use Illuminate\Http\Request;

class TarifWebController extends Controller
{
    public function index()
    {
        $tarifs = Tarif::all()->groupBy('categorie');
        return view('tarifs.index', compact('tarifs'));
    }

    public function create()
    {
        return view('tarifs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie' => 'required|in:congelation,transport,transport_ville,location',
            'designation' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'type' => 'nullable|string|in:avec_go,sans_go',
            'details' => 'nullable|json'
        ]);

        Tarif::create($validated);
        return redirect()->route('tarifs.index')->with('success', 'Tarif créé avec succès');
    }

    public function edit($id)
    {
        $tarif = Tarif::findOrFail($id);
        return view('tarifs.edit', compact('tarif'));
    }

    public function update(Request $request, $id)
    {
        $tarif = Tarif::findOrFail($id);
        $validated = $request->validate([
            'designation' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
        ]);
        $tarif->update($validated);
        return redirect()->route('tarifs.index')->with('success', 'Tarif mis à jour');
    }

    public function destroy($id)
    {
        $tarif = Tarif::findOrFail($id);
        $tarif->delete();
        return redirect()->route('tarifs.index')->with('success', 'Tarif supprimé');
    }
}
