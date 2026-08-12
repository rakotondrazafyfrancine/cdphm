@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid px-0">

    <!-- ==========================================
    EN-TÊTE
    ========================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">📊 Tableau de bord</h2>
            <p class="text-muted small">Vue d'ensemble de l'activité CDPHM</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                <i class="bi bi-calendar3 me-1"></i> {{ now()->format('d/m/Y') }}
            </span>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-clockwise"></i> Rafraîchir
            </button>
        </div>
    </div>

    <!-- ==========================================
    KPI PRINCIPAUX (3 grandes cartes colorées)
    ========================================== -->
    <div class="row g-4 mb-4">
        <!-- 1. Lots en stock -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 overflow-hidden" style="background: linear-gradient(145deg, #4e73df, #1a3a8a);">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h6 class="text-uppercase text-white-50 small fw-bold mb-1">📦 Lots en stock</h6>
                        <h2 class="display-4 fw-bold text-white mb-0">{{ $lotsActifs ?? 0 }}</h2>
                        <span class="text-white-50 small">en stock actuellement</span>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-3 p-3">
                        <i class="bi bi-box-seam fs-1 text-white"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3 pt-0 px-4">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-white" style="width: {{ min($lotsActifs ?? 0, 100) }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Clients enregistrés -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 overflow-hidden" style="background: linear-gradient(145deg, #1cc88a, #0f7a52);">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h6 class="text-uppercase text-white-50 small fw-bold mb-1">👥 Clients enregistrés</h6>
                        <h2 class="display-4 fw-bold text-white mb-0">{{ $totalClients ?? 0 }}</h2>
                        <span class="text-white-50 small">clients actifs</span>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-3 p-3">
                        <i class="bi bi-people fs-1 text-white"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3 pt-0 px-4">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-white" style="width: 45%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Poids en stock -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 overflow-hidden" style="background: linear-gradient(145deg, #f6c23e, #c49b0f);">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h6 class="text-uppercase text-white-50 small fw-bold mb-1">⚖️ Poids en stock</h6>
                        <h2 class="display-4 fw-bold text-white mb-0">{{ number_format($poidsStock ?? 0, 0) }}</h2>
                        <span class="text-white-50 small">kg</span>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-3 p-3">
                        <i class="bi bi-weight-scale fs-1 text-white"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3 pt-0 px-4">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-white" style="width: 70%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
    KPI SECONDAIRES (5 petites cartes)
    ========================================== -->
    <div class="row g-4 mb-4 justify-content-center">
        <!-- Tunnels -->
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 text-center p-3" style="background: #f0f7ff;">
                <div class="bg-primary bg-opacity-10 rounded-3 p-2 mb-2 d-inline-block mx-auto">
                    <i class="bi bi-thermometer-half fs-3 text-primary"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">{{ $totalTunnels ?? 0 }}</h5>
                <small class="text-muted">Tunnels</small>
            </div>
        </div>

        <!-- Chambres froides -->
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 text-center p-3" style="background: #eafaf1;">
                <div class="bg-info bg-opacity-10 rounded-3 p-2 mb-2 d-inline-block mx-auto">
                    <i class="bi bi-snow2 fs-3 text-info"></i>
                </div>
                <h5 class="fw-bold mb-0 text-info">{{ $totalChambres ?? 0 }}</h5>
                <small class="text-muted">Chambres froides</small>
            </div>
        </div>



        <!-- Produits -->
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 text-center p-3" style="background: #fef9e7;">
                <div class="bg-warning bg-opacity-10 rounded-3 p-2 mb-2 d-inline-block mx-auto">
                    <i class="bi bi-fish fs-3 text-warning"></i>
                </div>
                <h5 class="fw-bold mb-0 text-warning">{{ $totalProduits ?? 0 }}</h5>
                <small class="text-muted">Produits</small>
            </div>
        </div>

        <!-- Lots sortis -->
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card h-100 border-0 shadow-hover rounded-4 text-center p-3" style="background: #fdedec;">
                <div class="bg-dark bg-opacity-10 rounded-3 p-2 mb-2 d-inline-block mx-auto">
                    <i class="bi bi-check-circle fs-3 text-dark"></i>
                </div>
                <h5 class="fw-bold mb-0">{{ $lotsSortis ?? 0 }}</h5>
                <small class="text-muted">Lots sortis</small>
            </div>
        </div>
    </div>

    <!-- ==========================================
    ACTIVITÉ RÉCENTE — DERNIERS LOTS (Tableau stylé)
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i> Activité récente — Derniers lots
            </h5>
            <a href="{{ route('lots.index') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                Voir tout <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">N° Lot</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Client</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Produit</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Quantité (kg)</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Étape</th>
                            <th class="pe-4 py-3 text-uppercase small fw-bold text-muted">Date entrée</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($derniersLots ?? [] as $lot)
                            @php
                                $heures = $lot->created_at ? now()->diffInHours($lot->created_at) : 0;
                                $estEnRetard = $lot->statut === 'en_stock' && $heures > 24;
                                $numeroLot = sprintf('LOT-%04d', $lot->id); // ← Format LOT-0001
                            @endphp
                            <tr class="{{ $estEnRetard ? 'table-warning' : '' }}">
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">{{ $numeroLot }}</span>
                                </td>
                                <td>{{ $lot->client->nom ?? 'N/A' }}</td>
                                <td>{{ $lot->espece }}</td>
                                <td>{{ number_format($lot->poids_entree, 2) }}</td>
                                <td>
                                    @if($lot->statut === 'sorti')
                                        <span class="badge badge-modern badge-success">
                                            <i class="bi bi-check-circle me-1"></i> Sorti
                                        </span>
                                    @elseif($lot->statut === 'en_stock' && $lot->type_infrastructure === 'tunnel')
                                        <span class="badge badge-modern badge-warning">
                                            <i class="bi bi-thermometer-half me-1"></i> Congélation
                                            @if($estEnRetard)
                                                <span class="badge bg-danger rounded-pill ms-1" title="> 24h">!</span>
                                            @endif
                                        </span>
                                    @elseif($lot->statut === 'en_stock' && $lot->type_infrastructure === 'chambre')
                                        <span class="badge badge-modern badge-info">
                                            <i class="bi bi-snow2 me-1"></i> En chambre
                                        </span>
                                    @else
                                        <span class="badge badge-modern badge-secondary">{{ $lot->statut }}</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <small class="text-muted">{{ $lot->created_at ? $lot->created_at->format('d/m/Y') : 'N/A' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Aucun lot enregistré pour le moment.
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
    /* Effet de survol des cartes */
    .shadow-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .shadow-hover:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
    }
    .card {
        border-radius: 16px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-footer .progress {
        border-radius: 10px;
        background-color: rgba(255,255,255,0.2);
    }
    .card-footer .progress-bar {
        border-radius: 10px;
    }

    /* Style moderne du tableau */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
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
    .table-modern tbody tr {
        transition: background-color 0.2s ease;
    }
    .table-modern tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
    .table-modern tbody td {
        border-bottom: 1px solid #f1f3f5;
        padding: 0.75rem 0;
        vertical-align: middle;
    }
    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges modernes */
    .badge-modern {
        padding: 0.45rem 0.9rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-modern.badge-success {
        background: #d1f2eb;
        color: #0a6b4a;
    }
    .badge-modern.badge-warning {
        background: #fff3cd;
        color: #856404;
    }
    .badge-modern.badge-info {
        background: #cce5ff;
        color: #004085;
    }
    .badge-modern.badge-secondary {
        background: #e9ecef;
        color: #495057;
    }
    .badge-modern.badge-danger {
        background: #f8d7da;
        color: #721c24;
    }

    /* Alerte pour >24h */
    .table-warning {
        background-color: #fff9e6 !important;
    }
    .table-warning:hover {
        background-color: #ffedc9 !important;
    }

    /* Animation des cartes */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card {
        animation: fadeInUp 0.5s ease forwards;
    }
    /* Délai progressif pour les 8 cartes */
    .card:nth-child(1) { animation-delay: 0.05s; }
    .card:nth-child(2) { animation-delay: 0.10s; }
    .card:nth-child(3) { animation-delay: 0.15s; }
    .card:nth-child(4) { animation-delay: 0.20s; }
    .card:nth-child(5) { animation-delay: 0.25s; }
    .card:nth-child(6) { animation-delay: 0.30s; }
    .card:nth-child(7) { animation-delay: 0.35s; }
    .card:nth-child(8) { animation-delay: 0.40s; }

    /* Animation des lignes du tableau */
    .table-modern tbody tr {
        animation: fadeRow 0.4s ease forwards;
        opacity: 0;
    }
    .table-modern tbody tr:nth-child(1) { animation-delay: 0.05s; }
    .table-modern tbody tr:nth-child(2) { animation-delay: 0.10s; }
    .table-modern tbody tr:nth-child(3) { animation-delay: 0.15s; }
    .table-modern tbody tr:nth-child(4) { animation-delay: 0.20s; }
    .table-modern tbody tr:nth-child(5) { animation-delay: 0.25s; }
    .table-modern tbody tr:nth-child(6) { animation-delay: 0.30s; }
    .table-modern tbody tr:nth-child(7) { animation-delay: 0.35s; }
    .table-modern tbody tr:nth-child(8) { animation-delay: 0.40s; }
    .table-modern tbody tr:nth-child(9) { animation-delay: 0.45s; }
    .table-modern tbody tr:nth-child(10) { animation-delay: 0.50s; }

    @keyframes fadeRow {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
