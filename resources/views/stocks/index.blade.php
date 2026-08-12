@extends('layouts.app')

@section('title', 'Stock actuel')

@section('content')
<div class="container-fluid px-0">

    <!-- ==========================================
    BARRE DE RECHERCHE
    ========================================== -->
    <div class="mb-3">
        <input type="text" id="search-stock" class="form-control form-control-sm rounded-pill" style="max-width: 350px;" placeholder="🔍 Rechercher un lot, client, produit...">
    </div>

    <!-- ==========================================
    TABLEAU
    ========================================== -->
    <div style="overflow-x: auto; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
        <table id="table-stock" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">N° Lot</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">Client</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">Produit</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">Poids (kg)</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">Contre-pesée</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">Chambre</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left; padding: 12px 10px;">Entrée chambre</th>
                    <th style="background: #0d9488 !important; background-image: linear-gradient(135deg, #0d9488, #0284c7) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: center; padding: 12px 10px;">Impression</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 10px;"><strong style="color: #0f766e;">{{ $lot->numero }}</strong></td>
                        <td style="padding: 10px;">{{ $lot->client->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ $lot->espece }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->poids_entree, 2) }}</td>
                        <td style="padding: 10px;">
                            <span style="background: #ccfbf1; color: #0f766e; font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                                {{ number_format($lot->poids_sortie ?? $lot->poids_entree, 2) }} kg
                            </span>
                        </td>
                        <td style="padding: 10px;">
                            <span style="background: #ccfbf1; color: #0f766e; font-size: 12px; padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                                {{ $lot->chambre->nom ?? '—' }}
                            </span>
                        </td>
                        <td style="padding: 10px;">
                            <span style="color: #64748b; font-size: 12px;">
                                {{ $lot->date_entree_chambre ? $lot->date_entree_chambre->format('d/m/Y H:i') : '—' }}
                            </span>
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <a href="{{ route('lots.sortirChambre', $lot->id) }}" onclick="return confirm('Confirmer la sortie de ce lot ?');" style="background: #0d9488; color: white; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; white-space: nowrap;">
                                🖨️ Bon de sortie
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                            📭 Aucun lot en stock.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<style>
    .form-control, .form-select {
        border-radius: 30px !important;
    }
</style>

<script>
    document.getElementById('search-stock').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#table-stock tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection
