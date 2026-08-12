@extends('layouts.app')

@section('title', 'Modifier un client')

@section('content')
<div class="container">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">✏️ Modifier le client</h2>
            <p class="text-muted small">#{{ $client->id }} — {{ $client->nom }}</p>
        </div>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            ⬅️ Retour
        </a>
    </div>

    <!-- Formulaire -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold mb-0">📋 Informations du client</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('clients.update', $client->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-6">
                        <!-- Nom -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">👤 Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control rounded-pill" value="{{ old('nom', $client->nom) }}" required>
                            @error('nom')
                                <small class="text-danger">⚠️ {{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Contact -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">📞 Contact</label>
                            <input type="text" name="contact" class="form-control rounded-pill" value="{{ old('contact', $client->contact) }}">
                            @error('contact')
                                <small class="text-danger">⚠️ {{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Adresse -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">📍 Adresse</label>
                            <input type="text" name="adresse" class="form-control rounded-pill" value="{{ old('adresse', $client->adresse) }}">
                            @error('adresse')
                                <small class="text-danger">⚠️ {{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">📂 Type</label>
                            <select name="type" class="form-select rounded-pill">
                                <option value="collecteur" {{ old('type', $client->type) == 'collecteur' ? 'selected' : '' }}>Collecteur</option>
                                <option value="societe_peche" {{ old('type', $client->type) == 'societe_peche' ? 'selected' : '' }}>Societe peche</option>

                            </select>
                            @error('type')
                                <small class="text-danger">⚠️ {{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                        💾 Mettre à jour
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center text-muted small py-3 border-top mt-4">
        🗄️ CDPHM — {{ now()->format('Y') }} · Tous droits réservés
    </div>
</div>

<style>
    .form-control, .form-select {
        border-radius: 30px !important;
        padding: 0.55rem 1rem;
    }
    .btn {
        border-radius: 30px !important;
    }
    .card {
        border-radius: 16px !important;
    }
</style>
@endsection
