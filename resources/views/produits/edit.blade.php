@extends('layouts.app')

@section('title', 'Modifier le produit')

@section('content')
<div class="container">
    <h1>✏️ Modifier le produit</h1>

    <form action="{{ route('produits.update', $produit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nom du produit *</label>
            <input type="text" name="nom" class="form-control" value="{{ $produit->nom }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Catégorie *</label>
            <select name="categorie" class="form-control" required>
                <option value="poisson" {{ $produit->categorie == 'poisson' ? 'selected' : '' }}>🐟 Poisson</option>
                <option value="crevette" {{ $produit->categorie == 'crevette' ? 'selected' : '' }}>🦐 Crevette</option>
                <option value="crabe" {{ $produit->categorie == 'crabe' ? 'selected' : '' }}>🦀 Crabe</option>
                <option value="langouste" {{ $produit->categorie == 'langouste' ? 'selected' : '' }}>🦞 Langouste</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
        <a href="{{ route('produits.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
