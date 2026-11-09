@extends('layouts.app')

@section('title', isset($client) ? 'Modifier le client' : 'Nouveau client')

@section('content')
@php $edition = isset($client) && $client->exists; @endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $erreur)
                <li>{{ $erreur }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div class="card-header bg-white fw-semibold" style="color:#000;">
        {{ $edition ? 'Modifier la fiche du client' : 'Fiche du nouveau client' }}
    </div>
    <div class="card-body">
        <form action="{{ $edition ? route('clients.update', $client->id) : route('clients.store') }}" method="POST">
            @csrf
            @if($edition) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" style="color:#000;">Nom complet / Coopérative <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" style="border-radius:4px;" placeholder="Ex : Nouvel Armement" value="{{ old('nom', $client->nom ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" style="color:#000;">Numéro</label>
                    <input type="text" name="contact" class="form-control" style="border-radius:4px;" placeholder="Ex : +261 34 00 000 00" value="{{ old('contact', $client->contact ?? '') }}">
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary px-4" style="border-radius:4px;">Annuler</a>
                <button type="submit" class="btn text-white px-4" style="background:#1a3a5c;border-radius:4px;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
