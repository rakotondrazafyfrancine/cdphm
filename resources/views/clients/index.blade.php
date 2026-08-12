@extends('layouts.app')

@section('title', 'Gestion des clients')

@section('content')
<div class="container-fluid px-0">

    <!-- ==========================================
    EN-TÊTE
    ========================================== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">👤 Gestion des clients</h2>
            <p class="text-muted small">Liste des collecteurs et clients</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clients.create') }}" class="btn btn-primary rounded-pill px-4">
                ➕ Nouveau client
            </a>
        </div>
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

    <!-- ==========================================
    TABLEAU
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                📋 Liste des clients
                <span class="badge bg-secondary rounded-pill ms-2">{{ $clients->count() }}</span>
            </h5>
            <input type="text" id="search-client" class="form-control form-control-sm rounded-pill" style="width: 200px;" placeholder="🔍 Rechercher...">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-modern" id="table-clients">
                    <thead>
                        <tr>
                            <th class="ps-3 py-3">🆔 ID</th>
                            <th class="py-3">👤 Nom</th>
                            <th class="py-3">📞 Contact</th>
                            <th class="py-3">📍 Adresse</th>
                            <th class="py-3">📂 Type</th>
                            <th class="py-3">📦 Lots</th>
                            <th class="text-end pe-3 py-3">⚡ Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td class="ps-3"><span class="fw-bold text-primary">#{{ $client->id }}</span></td>
                                <td><strong>{{ $client->nom }}</strong></td>
                                <td>{{ $client->contact ?? 'N/A' }}</td>
                                <td>{{ $client->adresse ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">{{ $client->type ?? 'Particulier' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info rounded-pill">{{ $client->lots()->count() }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-outline-warning btn-sm rounded-pill px-2" title="Modifier">
                                        ✏️
                                    </a>
                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer définitivement {{ $client->nom }} ?')">
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
                                <td colspan="7" class="text-center py-4 text-muted">
                                    📭 Aucun client enregistré.
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
    .btn {
        border-radius: 30px !important;
    }
    .card {
        border-radius: 16px !important;
    }
</style>

<script>
    document.getElementById('search-client').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#table-clients tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection
