@extends('layouts.app')

@section('title', 'Modifier le tunnel : ' . $tunnel->nom)

@section('content')
<div class="container">
    <h1>✏️ Modifier le tunnel</h1>

    <form action="{{ route('tunnels.update', $tunnel->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nom du tunnel</label>
            <input type="text" name="nom" class="form-control" value="{{ $tunnel->nom }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Capacité (kg)</label>
            <input type="number" name="capacite" class="form-control" value="{{ $tunnel->capacite }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
                <option value="disponible" {{ $tunnel->statut == 'disponible' ? 'selected' : '' }}>Disponible</option>
                <option value="occupe" {{ $tunnel->statut == 'occupe' ? 'selected' : '' }}>Occupé</option>
                <option value="maintenance" {{ $tunnel->statut == 'maintenance' ? 'selected' : '' }}>En maintenance</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
        <a href="{{ route('tunnels.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
