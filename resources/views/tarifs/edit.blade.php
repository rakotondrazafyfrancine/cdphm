@extends('layouts.app')

@section('title', 'Modifier le tarif')

@section('content')
<div class="container">
    <h1>✏️ Modifier le tarif</h1>

    <form action="{{ route('tarifs.update', $tarif->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie" class="form-control" required>
                        <option value="congelation" {{ $tarif->categorie == 'congelation' ? 'selected' : '' }}>Congélation</option>
                        <option value="transport" {{ $tarif->categorie == 'transport' ? 'selected' : '' }}>Transport</option>
                        <option value="transport_ville" {{ $tarif->categorie == 'transport_ville' ? 'selected' : '' }}>Transport en ville</option>
                        <option value="location" {{ $tarif->categorie == 'location' ? 'selected' : '' }}>Location de camions</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Type (optionnel)</label>
                    <select name="type" class="form-control">
                        <option value="">Standard</option>
                        <option value="avec_go" {{ $tarif->type == 'avec_go' ? 'selected' : '' }}>Avec GO</option>
                        <option value="sans_go" {{ $tarif->type == 'sans_go' ? 'selected' : '' }}>Sans GO</option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Désignation *</label>
                    <input type="text" name="designation" class="form-control" value="{{ $tarif->designation }}" required>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Montant (Ar) *</label>
                    <input type="number" name="montant" class="form-control" step="0.01" min="0" value="{{ $tarif->montant }}" required>
                </div>
            </div>

            <div class="col-md-12 mt-3">
                <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
                <a href="{{ route('tarifs.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </div>
    </form>
</div>
@endsection
