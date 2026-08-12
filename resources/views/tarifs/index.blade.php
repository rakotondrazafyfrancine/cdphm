@extends('layouts.app')

@section('title', 'Gestion des tarifs')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📊 Gestion des tarifs</h1>
        <a href="{{ route('tarifs.create') }}" class="btn btn-primary">➕ Nouveau tarif</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($tarifs as $categorie => $items)
        <div class="card mb-4">
            <div class="card-header
                @switch($categorie)
                    @case('congelation') bg-warning @break
                    @case('transport') bg-info @break
                    @case('transport_ville') bg-success @break
                    @case('location') bg-secondary @break
                    @default bg-primary
                @endswitch
                text-white">
                <h5 class="mb-0">
                    @switch($categorie)
                        @case('congelation') ❄️ Congélation @break
                        @case('transport') 🚛 Transport @break
                        @case('transport_ville') 🏙️ Transport en ville @break
                        @case('location') 🚚 Location de camions @break
                        @default {{ ucfirst($categorie) }}
                    @endswitch
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Type</th>
                            <th>Montant (Ar)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $tarif)
                            <tr>
                                <td>{{ $tarif->designation }}</td>
                                <td>
                                    @if($tarif->type)
                                        <span class="badge bg-secondary">{{ $tarif->type }}</span>
                                    @else
                                        <span class="badge bg-light text-dark">Standard</span>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($tarif->montant, 2) }}</strong></td>
                                <td>
                                    <a href="{{ route('tarifs.edit', $tarif->id) }}" class="btn btn-sm btn-warning">✏️ Modifier</a>
                                    <form action="{{ route('tarifs.destroy', $tarif->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce tarif ?')">🗑️ Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Aucun tarif enregistré.</div>
    @endforelse
</div>
@endsection
