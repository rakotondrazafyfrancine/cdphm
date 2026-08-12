@extends('layouts.app')

@section('title', 'Entrées en congélation')

@section('content')
<div class="container-fluid px-0">

    <!-- ==========================================
    EN-TÊTE
    ========================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">🚛 Nouvelle Arrivée Quai (Fiche Contrôle)</h2>
            <p class="text-muted small">Enregistrement des lots en congélation</p>
        </div>
        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
            📅 {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>

    <!-- ==========================================
    MESSAGES FLASH
    ========================================== -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
            ❌ {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ==========================================
    GRILLE : FORMULAIRE (gauche) | TABLEAU (droite)
    ========================================== -->
    <div class="row g-4">

        <!-- ==========================================
        COLONNE GAUCHE : FORMULAIRE
        ========================================== -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">📋 Nouvelle Arrivée Quai (Fiche Contrôle)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lots.store') }}" method="POST">
                        @csrf

                        <!-- ===== CLIENT ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">👤 Collecteur / Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select rounded-pill" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== CONTACT ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">📞 Numéro de Contact du Client</label>
                            <input type="text" name="contact" class="form-control rounded-pill" placeholder="Ex: +261 34 xx xxx" value="{{ old('contact') }}">
                            @error('contact') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== ESPÈCE (saisie libre) ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">🐟 Espèce / Produit <span class="text-danger">*</span></label>
                            <input type="text" name="espece" class="form-control rounded-pill" placeholder="Ex: Poisson Capitaine, Crevette royale..." value="{{ old('espece') }}" required>
                            @error('espece') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== DÉCOMPTE ===== -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="fw-bold small text-muted">🎯 Nb Filets</label>
                                <input type="number" name="nb_filets" class="form-control form-control-sm rounded-pill" value="{{ old('nb_filets', 0) }}" min="0">
                            </div>
                            <div class="col-4">
                                <label class="fw-bold small text-muted">🐠 Nb Poissons</label>
                                <input type="number" name="nb_poissons" class="form-control form-control-sm rounded-pill" value="{{ old('nb_poissons', 0) }}" min="0">
                            </div>
                            <div class="col-4">
                                <label class="fw-bold small text-muted">📦 Nb Bacs</label>
                                <input type="number" name="nb_bacs" class="form-control form-control-sm rounded-pill" value="{{ old('nb_bacs', 0) }}" min="0">
                            </div>
                        </div>

                        <!-- ===== POIDS BRUT ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">⚖️ Poids Brut Entrée (kg) <span class="text-danger">*</span></label>
                            <input type="number" name="poids_entree" class="form-control rounded-pill" id="poids-input" step="0.01" min="0.1" placeholder="Ex: 450" value="{{ old('poids_entree') }}" required>
                            @error('poids_entree') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== TUNNEL (obligatoire) ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">🌡️ Tunnel de congélation <span class="text-danger">*</span></label>
                            <select name="tunnel_id" class="form-select rounded-pill" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($tunnels as $tunnel)
                                    <option value="{{ $tunnel->id }}" {{ old('tunnel_id') == $tunnel->id ? 'selected' : '' }}>
                                        {{ $tunnel->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tunnel_id') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== CHAMBRE FROIDE (optionnelle) ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">❄️ Chambre froide (optionnel)</label>
                            <select name="chambre_froide_id" class="form-select rounded-pill">
                                <option value="">-- Aucune --</option>
                                @foreach($chambres as $chambre)
                                    <option value="{{ $chambre->id }}" {{ old('chambre_froide_id') == $chambre->id ? 'selected' : '' }}>
                                        {{ $chambre->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ===== DURÉE ===== -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="fw-bold small text-muted">⏱️ Durée (Heures)</label>
                                <input type="number" name="heures_tunnel" class="form-control form-control-sm rounded-pill" value="{{ old('heures_tunnel', 24) }}" min="1" id="heures-input">
                            </div>
                            <div class="col-6">
                                <label class="fw-bold small text-muted">📅 Durée (Fraction Jour)</label>
                                <input type="text" class="form-control form-control-sm rounded-pill" id="jours-input" value="1" readonly>
                            </div>
                        </div>

                        <!-- ===== TARIF FIXE TUNNEL ===== -->
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 mb-3 text-center border border-warning border-opacity-25">
                            <small class="text-muted d-block">🌡️ Tarif fixe Tunnel (Ar/kg/jour)</small>
                            <strong class="fs-3 text-warning">450 Ar/kg/jour</strong>
                        </div>

                        <!-- ===== FACTURATION ESTIMÉE ===== -->
                        <div class="bg-light rounded-3 p-3 mb-3 text-center">
                            <small class="text-muted d-block">💰 Facturation estimée</small>
                            <strong class="fs-4 text-primary" id="estimation">0 Ariary</strong>
                        </div>

                        <!-- ===== BOUTON VALIDER ===== -->
                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2">
                            ✅ Valider l'Entrée & Générer Bon
                        </button>
                    </form>
                </div>
            </div>

            <!-- ==========================================
            ENREGISTRER UN CLIENT (Quai)
            ========================================== -->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">➕ Enregistrer un Client (Quai)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('clients.store') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label fw-bold small">Nom complet / Coopérative</label>
                            <input type="text" name="nom" class="form-control rounded-pill" placeholder="Ex: Nouvel Armement..." required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100 rounded-pill">
                            ➕ Créer le Compte Immédiatement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ==========================================
        COLONNE DROITE : TABLEAU
        ========================================== -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">📋 Suivi des Arrivées Physiques</h5>
                    <input type="text" id="search-table" class="form-control form-control-sm rounded-pill" style="width: 200px;" placeholder="🔍 Rechercher...">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-modern" id="table-entrees">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-3">📋 ID Lot</th>
                                    <th class="py-3">👤 Client / Contact</th>
                                    <th class="py-3">📦 Décompte</th>
                                    <th class="py-3">⚖️ Poids</th>
                                    <th class="py-3">👷 Manutention</th>
                                    <th class="py-3">⏱️ Alerte Stock</th>
                                    <th class="text-center py-3">🖨️</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lots as $lot)
                                    @php
                                        $heures = $lot->created_at ? now()->diffInHours($lot->created_at) : 0;
                                        $alerte = $heures > 24 ? '⚠️ ' . floor($heures / 24) . 'j ' . ($heures % 24) . 'h' : floor($heures / 24) . 'j ' . ($heures % 24) . 'h';
                                    @endphp
                                    <tr>
                                        <td class="ps-3"><span class="fw-bold text-primary">{{ $lot->numero }}</span></td>
                                        <td>
                                            <strong>{{ $lot->client->nom ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $lot->contact ?? 'N/A' }}</small>
                                        </td>
                                        <td>F:{{ $lot->nb_filets ?? 0 }} P:{{ $lot->nb_poissons ?? 0 }} B:{{ $lot->nb_bacs ?? 0 }}</td>
                                        <td><strong>{{ number_format($lot->poids_entree, 2) }} kg</strong></td>
                                        <td>{{ $lot->dockers ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $heures > 24 ? 'bg-danger' : 'bg-secondary' }} rounded-pill">
                                                {{ $alerte }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('lot.print', $lot->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2" title="Bon d'entrée">
                                                🖨️
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">📭 Aucune entrée récente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

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
    .table-modern {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
    }
    .table-modern thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        font-weight: 700;
        padding: 0.75rem 0.5rem;
    }
    .table-modern tbody td {
        padding: 0.6rem 0.5rem;
        border-bottom: 1px solid #f1f3f5;
        vertical-align: middle;
    }
    .table-modern tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
    }
    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }

    .form-control, .form-select {
        border-radius: 30px !important;
        padding: 0.55rem 1rem;
    }
    .form-control-sm {
        border-radius: 30px !important;
    }
    .btn {
        border-radius: 30px !important;
    }
    .card {
        border-radius: 16px !important;
    }
</style>

<!-- ==========================================
SCRIPTS
========================================== -->
<script>
    // ===== RECHERCHE EN TEMPS RÉEL =====
    document.getElementById('search-table').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#table-entrees tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });

    // ===== CALCUL DE LA FACTURATION ESTIMÉE =====
    document.addEventListener('DOMContentLoaded', function() {
        const poidsInput = document.getElementById('poids-input');
        const heuresInput = document.getElementById('heures-input');
        const joursInput = document.getElementById('jours-input');
        const estimationSpan = document.getElementById('estimation');

        function updateEstimation() {
            const poids = parseFloat(poidsInput.value) || 0;
            const heures = parseFloat(heuresInput.value) || 24;
            const jours = Math.max(heures / 24, 1 / 24);
            const montant = poids * jours * 450;

            joursInput.value = jours.toFixed(2);
            estimationSpan.textContent = Math.round(montant).toLocaleString() + ' Ariary';
        }

        poidsInput.addEventListener('input', updateEstimation);
        heuresInput.addEventListener('input', updateEstimation);
        updateEstimation();
    });
</script>
@endsection
