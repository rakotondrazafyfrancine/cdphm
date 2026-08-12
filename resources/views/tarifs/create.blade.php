@extends('layouts.app')

@section('title', 'Créer un tarif')

@section('content')
<div class="container">
    <h1>➕ Créer un tarif</h1>

    <form action="{{ route('tarifs.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie" class="form-control" required>
                        <option value="congelation">Congélation</option>
                        <option value="transport">Transport</option>
                        <option value="transport_ville">Transport en ville</option>
                        <option value="location">Location de camions</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Type (optionnel)</label>
                    <select name="type" class="form-control">
                        <option value="">Standard</option>
                        <option value="avec_go">Avec GO</option>
                        <option value="sans_go">Sans GO</option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Désignation *</label>
                    <input type="text" name="designation" class="form-control" placeholder="Ex: Mahajanga - Tamanarivo" required>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label>Montant (Ar) *</label>
                    <input type="number" name="montant" class="form-control" step="0.01" min="0" required>
                </div>
            </div>

            <div class="col-md-12 mt-3">
                <button type="submit" class="btn btn-success">💾 Enregistrer</button>
                <a href="{{ route('tarifs.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </div>
    </form>
</div>
@endsection
