@extends('layouts.app')

@section('title', 'Ajouter un produit')

@section('content')
<div class="container">
    <h1>➕ Ajouter un produit</h1>

    <form action="{{ route('produits.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nom du produit *</label>
            <input type="text" name="nom" class="form-control" placeholder="Ex: Capitaine, Crevette royale..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Catégorie *</label>
            <select name="categorie" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="poisson">🐟 Poisson</option>
                <option value="crevette">🦐 Crevette</option>
                <option value="crabe">🦀 Crabe</option>
                <option value="langouste">🦞 Langouste</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">💾 Enregistrer</button>
        <a href="{{ route('produits.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
