@extends('layouts.app')

@section('title', 'Entrées / Congélation')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-semibold mb-0" style="color:#1a3a5c;">Suivi des arrivées physiques</h5>
        <small class="text-muted">{{ $lots->total() }} lot(s) présent(s)</small>
    </div>
    <a href="{{ route('lots.create') }}" class="btn btn-sm text-white px-3" style="background:#1a3a5c;border-radius:4px;">+ Nouvelle arrivée</a>
</div>

<div class="card border-0" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div class="card-body border-bottom py-2">
        <input type="text" id="recherche" class="form-control form-control-sm" style="max-width:280px;border-radius:4px;" placeholder="Rechercher dans la page...">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-entrees">
            <thead>
                <tr class="small text-uppercase" style="background:#1a3a5c;color:#fff;">
                    <th class="ps-3 py-2 fw-semibold text-white" style="background:#1a3a5c;">N° Lot</th>
                    <th class="py-2 fw-semibold text-white" style="background:#1a3a5c;">Client / Contact</th>
                    <th class="py-2 fw-semibold text-white" style="background:#1a3a5c;">Produit</th>
                    <th class="py-2 fw-semibold text-white" style="background:#1a3a5c;">Poids</th>
                    <th class="py-2 fw-semibold text-white" style="background:#1a3a5c;">Alerte stock</th>
                    <th class="py-2 text-center fw-semibold text-white" style="background:#1a3a5c;">Impression</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                    @php
                        $heures = $lot->created_at ? (int) now()->diffInHours($lot->created_at, true) : 0;
                        $duree = intdiv($heures, 24) . ' j ' . ($heures % 24) . ' h';
                        $enRetard = !$lot->chambre_id && $heures > 24;
                    @endphp
                    <tr>
                        <td class="ps-3 fw-semibold" style="color:#1a3a5c;">{{ $lot->numero }}</td>
                        <td>
                            {{ $lot->client->nom ?? '—' }}<br>
                            <small class="text-muted">{{ $lot->contact ?? '—' }}</small>
                        </td>
                        <td>{{ $lot->espece }}</td>
                        <td class="fw-semibold">{{ number_format($lot->poids_entree, 2) }} kg</td>
                        <td>
                            <span class="small px-2 py-1" style="border-radius:3px;background:{{ $enRetard ? '#fee2e2' : '#e8eef5' }};color:{{ $enRetard ? '#b91c1c' : '#1a3a5c' }};">
                                {{ $duree }}{{ $enRetard ? ' (plus de 24 h)' : '' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('lots.print', $lot->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:4px;">Bon d'entrée</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Aucune entrée récente.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Affichage de {{ $lots->firstItem() ?? 0 }} à {{ $lots->lastItem() ?? 0 }} sur {{ $lots->total() }}</small>
        {{ $lots->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
    document.getElementById('recherche').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#table-entrees tbody tr').forEach(function (tr) {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endsection
