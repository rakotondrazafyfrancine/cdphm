@extends('layouts.app')

@section('title', 'Détails du lot ' . $lot->numero)

@section('content')
<div class="container">
    <!-- ==========================================
    EN-TÊTE
    ========================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">📄 Détails du lot</h2>
            <p class="text-muted small">{{ $lot->numero }}</p>
        </div>
        <div>
            <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Retour au stock
            </a>
            @if($lot->statut === 'en_stock')
                <a href="{{ route('lot.bonSortie', $lot->id) }}" class="btn btn-success btn-sm rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> Bon de sortie
                </a>
            @else
                <span class="badge bg-secondary rounded-pill px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i> Déjà sorti
                </span>
            @endif
        </div>
    </div>

    <!-- ==========================================
    CARTE DES INFORMATIONS
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="row g-4">

                <!-- Colonne gauche -->
                <div class="col-md-6">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i> Informations générales</h6>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 140px;">N° Lot</th>
                            <td><strong>{{ $lot->numero }}</strong></td>
                        </tr>
                        <tr>
                            <th>Client</th>
                            <td>{{ $lot->client->nom ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Contact</th>
                            <td>{{ $lot->contact ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Produit / Espèce</th>
                            <td>{{ $lot->espece }}</td>
                        </tr>
                        <tr>
                            <th>Catégorie</th>
                            <td>
                                <span class="badge bg-secondary rounded-pill">{{ $lot->categorie }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dockers</th>
                            <td>{{ $lot->dockers ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Colonne droite -->
                <div class="col-md-6">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-box-seam me-2"></i> Stockage</h6>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 140px;">Poids entrée</th>
                            <td><strong>{{ number_format($lot->poids_entree, 2) }} kg</strong></td>
                        </tr>
                        <tr>
                            <th>Poids sortie</th>
                            <td>{{ $lot->poids_sortie ? number_format($lot->poids_sortie, 2) . ' kg' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Chambre froide</th>
                            <td>{{ $lot->chambre->nom ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date entrée</th>
                            <td>{{ $lot->date_entree_chambre ? \Carbon\Carbon::parse($lot->date_entree_chambre)->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date sortie</th>
                            <td>
                                @if($lot->date_sortie)
                                    {{ \Carbon\Carbon::parse($lot->date_sortie)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">En cours de stockage</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($lot->statut === 'en_stock')
                                    <span class="badge bg-warning rounded-pill">🟡 En stock</span>
                                @else
                                    <span class="badge bg-success rounded-pill">✅ Sorti</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- ==========================================
                RÉCAPITULATIF FINANCIER (si sorti)
                ========================================== -->
                @if($lot->statut === 'sorti' && $jours)
                    <div class="col-12">
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <small class="text-muted d-block">Durée de stockage</small>
                                    <strong class="fs-4">{{ $jours }} jour(s)</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3 text-center">
                                    <small class="text-muted d-block">Tarif appliqué</small>
                                    <strong class="fs-4">{{ number_format($tarifChambre, 0) }} Ar/kg/j</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3 text-center border border-success border-opacity-25">
                                    <small class="text-muted d-block">💰 Montant facturé</small>
                                    <strong class="fs-4 text-success">{{ number_format($montant, 0) }} Ar</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- ==========================================
    PIED DE PAGE
    ========================================== -->
    <div class="text-center text-muted small py-3 border-top mt-4">
        <i class="bi bi-database me-1"></i> CDPHM — {{ now()->format('Y') }} · Tous droits réservés
    </div>
</div>

<!-- ==========================================
STYLES PERSONNALISÉS
========================================== -->
<style>
    .card {
        border-radius: 16px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .table th {
        font-weight: 600;
        color: #1a3a5c;
    }
    .table td {
        font-weight: 500;
        color: #212529;
    }
    .table tr {
        border-bottom: 1px solid #f1f3f5;
    }
    .table tr:last-child {
        border-bottom: none;
    }
</style>
@endsection
