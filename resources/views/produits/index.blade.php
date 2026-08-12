@extends('layouts.app')

@section('title', 'Gestion des produits')

@section('content')
<div class="container-fluid px-0">

    <!-- ==========================================
    EN-TÊTE
    ========================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">🐟 Gestion des produits</h2>
            <p class="text-muted small">Ajouter et gérer les espèces disponibles</p>
        </div>
        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('d/m/Y') }}
        </span>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ==========================================
    FORMULAIRE D'AJOUT (en haut)
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle text-primary me-2"></i> Ajouter un produit</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('produits.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-5">
                    <label class="form-label fw-bold small">Nom du produit</label>
                    <input type="text" name="nom" class="form-control" placeholder="Ex: Capitaine, Crevette royale..." required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Catégorie</label>
                    <select name="categorie" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="poisson">🐟 Poisson</option>
                        <option value="crevette">🦐 Crevette</option>
                        <option value="crabe">🦀 Crabe</option>
                        <option value="langouste">🦞 Langouste</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================
    TABLEAU DES PRODUITS (en bas)
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-list-ul text-primary me-2"></i> Liste des produits
                <span class="badge bg-secondary rounded-pill ms-2">{{ $produits->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">ID</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Nom</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Catégorie</th>
                            <th class="pe-4 py-3 text-uppercase small fw-bold text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produits as $produit)
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-primary">#{{ $produit->id }}</span></td>
                                <td>{{ $produit->nom }}</td>
                                <td>
                                    <span class="badge badge-modern badge-{{ $produit->categorie }}">
                                        {{ $produit->categorie }}
                                    </span>
                                </td>
                                <td class="pe-4">
                                    <a href="{{ route('produits.edit', $produit->id) }}" class="btn btn-outline-warning btn-sm rounded-pill px-2" title="Modifier">
                                        ✏️
                                    </a>
                                    <form action="{{ route('produits.destroy', $produit->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer le produit {{ $produit->nom }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Supprimer">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Aucun produit enregistré. Ajoutez-en un ci-dessus !
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==========================================
    PIED DE PAGE
    ========================================== -->
    <div class="text-center text-muted small py-4 border-top mt-4">
        <i class="bi bi-database me-1"></i> CDPHM — {{ now()->format('Y') }} · Tous droits réservés
    </div>

</div>

<!-- ==========================================
STYLES PERSONNALISÉS
========================================== -->
<style>
    .badge-modern {
        padding: 0.4rem 0.9rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    .badge-poisson {
        background: #cce5ff;
        color: #004085;
    }
    .badge-crevette {
        background: #f8d7da;
        color: #721c24;
    }
    .badge-crabe {
        background: #d1f2eb;
        color: #0a6b4a;
    }
    .badge-langouste {
        background: #fff3cd;
        color: #856404;
    }
    .table-modern thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 700;
    }
    .table-modern tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
    .card {
        border-radius: 16px !important;
    }
    .form-control, .form-select {
        border-radius: 30px !important;
        padding: 0.55rem 1rem;
    }
    .btn {
        border-radius: 30px !important;
    }
</style>
@endsection
