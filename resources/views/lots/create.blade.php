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
                            <label class="form-label fw-bold small">📞 Contact </label>
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

                        <!-- ===== ÉQUIPE DE MANUTENTION ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">👷 Équipe de manutention</label>
                            <div class="input-group manutention-group">
                                <span class="input-group-text">👷</span>
                                <input type="text" name="equipe_manutention" class="form-control rounded-end-pill" placeholder="Ex: Équipe A - Rakoto & Rasoa" value="{{ old('equipe_manutention') }}">
                            </div>
                            @error('equipe_manutention') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== TUNNEL (optionnel si chambre choisie) ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">🌡️ Tunnel de congélation</label>
                            <select name="tunnel_id" class="form-select rounded-pill">
                                <option value="">-- Sélectionner --</option>
                                @foreach($tunnels as $tunnel)
                                    <option value="{{ $tunnel->id }}" {{ old('tunnel_id', $tunnelSelectionne ?? '') == $tunnel->id ? 'selected' : '' }}>
                                        {{ $tunnel->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tunnel_id') <small class="text-danger">⚠️ {{ $message }}</small> @enderror
                        </div>

                        <!-- ===== CHAMBRE FROIDE (optionnelle) ===== -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">❄️ Chambre froide (optionnel)</label>
                            <select name="chambre_id" class="form-select rounded-pill">
                                <option value="">-- Choisir--</option>
                                @foreach($chambres as $chambre)
                                    <option value="{{ $chambre->id }}" {{ old('chambre_id', $chambreSelectionne ?? '') == $chambre->id ? 'selected' : '' }}>
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
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                    <div style="font-size: 17px; font-weight: 600; color: #1e293b;">
                        📋 Suivi des Arrivées Physiques
                    </div>
                    <input type="text" id="search-table" style="width: 200px; padding: 6px 14px; border-radius: 20px; border: 1px solid #cbd5e1; font-size: 13px;" placeholder="🔍 Rechercher...">
                </div>

                <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;" id="table-entrees">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #0d9488, #0284c7);">
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">ID Lot</th>
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Client / Contact</th>
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Décompte</th>
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Poids</th>
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Manutention</th>
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Alerte Stock</th>
                                <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: center;">Impression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lots as $lot)
                                @php
                                    $heures = $lot->created_at ? now()->diffInHours($lot->created_at, absolute: true ) : 0;
                                    $alerte = $heures > 24 ? '⚠️ ' . floor($heures / 24) . 'j ' . ($heures % 24) . 'h' : floor($heures / 24) . 'j ' . ($heures % 24) . 'h';
                                @endphp
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 10px;"><strong style="color: #0f766e;">{{ $lot->numero }}</strong></td>
                                    <td style="padding: 10px;">
                                        <strong>{{ $lot->client->nom ?? '—' }}</strong>
                                        <br>
                                        <span style="color: #64748b; font-size: 12px;">{{ $lot->contact ?? '—' }}</span>
                                    </td>
                                    <td style="padding: 10px; color: #475569;">F:{{ $lot->nb_filets ?? 0 }} P:{{ $lot->nb_poissons ?? 0 }} B:{{ $lot->nb_bacs ?? 0 }}</td>
                                    <td style="padding: 10px;"><strong>{{ number_format($lot->poids_entree, 2) }} kg</strong></td>
                                    <td style="padding: 10px;">{{ $lot->equipe_manutention ?? '—' }}</td>
                                    <td style="padding: 10px;">
                                        <span style="background: {{ $heures > 24 ? '#fee2e2' : '#ccfbf1' }}; color: {{ $heures > 24 ? '#b91c1c' : '#0f766e' }}; font-weight: 600; font-size: 12px; padding: 2px 10px; border-radius: 20px;">
                                            {{ $alerte }}
                                        </span>
                                    </td>
                                    <td style="padding: 10px; text-align: center;">
                                        <a href="{{ route('lots.print', $lot->id) }}" style="background: #0284c7; color: white; padding: 6px 10px; border-radius: 8px; text-decoration: none; font-size: 12px; white-space: nowrap;">
                                            🖨️ Bon d'entrée
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">📭 Aucune entrée récente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>



<!-- ==========================================
STYLES
========================================== -->
<style>
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
    .manutention-group .input-group-text {
        background: linear-gradient(135deg, #0d9488, #0284c7);
        color: white;
        border: none;
        border-radius: 30px 0 0 30px;
        font-size: 0.95rem;
    }
    .manutention-group .form-control {
        border-left: none;
    }
    .manutention-group .form-control:focus {
        box-shadow: none;
        border-color: #0d9488;
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
        const tunnelSelect = document.querySelector('select[name="tunnel_id"]');
        const chambreSelect = document.querySelector('select[name="chambre_id"]');

        function updateEstimation() {
            const poids = parseFloat(poidsInput.value) || 0;
            const heures = parseFloat(heuresInput.value) || 24;
            const jours = Math.max(heures / 24, 1 / 24);

            let montant = 0;
            if (chambreSelect.value && !tunnelSelect.value) {
                montant = poids * 25;
                joursInput.value = '—';
            } else {
                montant = poids * jours * 450;
                joursInput.value = jours.toFixed(2);
            }

            estimationSpan.textContent = Math.round(montant).toLocaleString() + ' Ariary';
        }

        poidsInput.addEventListener('input', updateEstimation);
        heuresInput.addEventListener('input', updateEstimation);
        tunnelSelect.addEventListener('change', updateEstimation);
        chambreSelect.addEventListener('change', updateEstimation);
        updateEstimation();
    });
</script>
@endsection
