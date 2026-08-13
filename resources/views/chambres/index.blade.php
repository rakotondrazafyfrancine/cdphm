@extends('layouts.app')

@section('title', 'Chambres froides')

@section('content')
<div class="container-fluid px-0">

    <!-- ==========================================
    EN-TÊTE
    ========================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">🧊 Chambres froides</h2>
            <p class="text-muted small">Capacité et occupation</p>
        </div>
        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
            📅 {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>

    <!-- ==========================================
    GRILLE DES CHAMBRES (2 colonnes)
    ========================================== -->
    <div class="row g-4">

        @forelse($chambres as $chambre)
            @php
                $poidsActuel = $chambre->lots()->where('statut', 'en_stock')->sum('poids_entree');
                $capaciteKg = $chambre->capacite ?? 0;
                $taux = $capaciteKg > 0 ? round(($poidsActuel / $capaciteKg) * 100, 1) : 0;
                $couleur = $taux > 80 ? 'danger' : ($taux > 50 ? 'warning' : 'success');
            @endphp

            <div class="col-md-6">
                <div class="card border-0 shadow-hover rounded-4 h-100">
                    <div class="card-body p-4">

                        <!-- En-tête -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">
                                    🧊 {{ $chambre->nom }}
                                    <span class="badge {{ $chambre->statut === 'disponible' ? 'bg-success' : ($chambre->statut === 'pleine' ? 'bg-danger' : 'bg-warning') }} rounded-pill ms-2">
                                        {{ ucfirst($chambre->statut) }}
                                    </span>
                                </h5>
                                <small class="text-muted">ID: {{ $chambre->id }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    {{ number_format($capaciteKg, 0) }} kg
                                </span>
                            </div>
                        </div>

                        <!-- Barre de progression -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Occupation</span>
                                <span class="fw-bold text-{{ $couleur }}">
                                    {{ $taux }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 8px;">
                                <div class="progress-bar bg-{{ $couleur }} rounded-pill"
                                     style="width: {{ min($taux, 100) }}%; transition: width 0.6s ease;">
                                </div>
                            </div>
                        </div>

                        <!-- Poids -->
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-2 text-center">
                                    <small class="text-muted d-block">Stocké</small>
                                    <strong>{{ number_format($poidsActuel, 0) }} kg</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-2 text-center">
                                    <small class="text-muted d-block">Restant</small>
                                    <strong>{{ number_format(max(0, $capaciteKg - $poidsActuel), 0) }} kg</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton Voir -->
                        <div class="mt-3">
                            <a href="{{ route('chambres.show', $chambre->id) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                👁️ Voir les lots
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            @if(($loop->index + 1) % 2 == 0 && !$loop->last)
                <div class="w-100"></div>
            @endif

        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-snow2 fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">Aucune chambre froide enregistrée</h5>
                    </div>
                </div>
            </div>
        @endforelse

    </div>

    <!-- ==========================================
    PIED DE PAGE
    ========================================== -->
    <div class="text-center text-muted small py-3 border-top mt-4">
        🗄️ CDPHM — {{ now()->format('Y') }} · Tous droits réservés
    </div>
</div>

<!-- ==========================================
STYLES
========================================== -->
<style>
    .shadow-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .shadow-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.10) !important;
    }
    .card {
        border-radius: 16px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }
    .progress-bar {
        border-radius: 8px;
        transition: width 0.6s ease;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endsection
