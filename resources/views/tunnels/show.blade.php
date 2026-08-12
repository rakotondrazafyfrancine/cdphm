@extends('layouts.app')
@section('title', 'Tunnel : ' . $tunnel->nom)

@section('content')

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">

    <!-- En-tête -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
        <div style="font-size: 17px; font-weight: 600; color: #1e293b;">
            ❄️ {{ $tunnel->nom }}
        </div>
        <a href="{{ route('tunnels.index') }}" style="background: #f1f5f9; color: #1e293b; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 13px;">
            ← Retour
        </a>
    </div>

    <!-- 3 cartes : Taux | Poids actuel | Capacité -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px;">
        <div style="background: #f0fdfa; border: 1px solid #0d9488; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 22px;">📈</div>
            <div style="font-size: 22px; font-weight: 700;">{{ $taux }}%</div>
            <div style="font-size: 13px; color: #64748b;">Taux de remplissage</div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 22px;">⚖️</div>
            <div style="font-size: 22px; font-weight: 700;">{{ number_format($poidsActuel, 0, ',', ' ') }} kg</div>
            <div style="font-size: 13px; color: #64748b;">Poids actuel</div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 22px;">📏</div>
            <div style="font-size: 22px; font-weight: 700;">{{ number_format($capaciteKg, 0, ',', ' ') }} kg</div>
            <div style="font-size: 13px; color: #64748b;">Capacité totale</div>
        </div>
    </div>
</div>

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-top: 20px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
    <div style="font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 16px;">
        📋 Lots en congélation
        <span style="background: #e2e8f0; color: #475569; font-size: 12px; padding: 2px 10px; border-radius: 20px; margin-left: 6px;">{{ $lots->count() }}</span>
    </div>

    <a href="{{ route('entrees.index') }}" style="text-decoration: none;">
        <div style="border: 2px dashed #0d9488; border-radius: 12px; padding: 16px; text-align: center; margin-bottom: 20px; color: #0d9488; font-weight: 600;">
            ➕ Ajouter un lot
        </div>
    </a>

    <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #0d9488, #0284c7);">
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">N° Lot</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Client</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Produit</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Quantité (kg)</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Date entrée</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Depuis</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Alerte</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: center;">Impression</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                    @php
                        $heures = $lot->created_at ? now()->diffInHours($lot->created_at, absolute: true) : 0;
                        $alerte = $heures > 24;

                    @endphp
                    <tr style="border-bottom: 1px solid #e2e8f0; {{ $alerte ? 'background: #fef2f2;' : '' }} transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ $alerte ? '#fef2f2' : 'white' }}'">
                        <td style="padding: 10px;"><strong>{{ $lot->numero }}</strong></td>
                        <td style="padding: 10px;">{{ $lot->client->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ $lot->espece ?? '—' }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->poids_entree, 2) }}</td>
                        <td style="padding: 10px;">{{ $lot->created_at ? $lot->created_at->format('d/m/Y') : '—' }}</td>
                        <td style="padding: 10px;">
                            <span style="background: #f1f5f9; color: #475569; font-size: 12px; padding: 2px 10px; border-radius: 20px;">
                                {{ $lot->created_at ? $lot->created_at->diffForHumans() : '—' }}
                            </span>
                        </td>
                        <td style="padding: 10px;">
                            @if($alerte)
                                <span style="background: #fee2e2; color: #b91c1c; font-weight: 600; font-size: 12px; padding: 2px 10px; border-radius: 20px;">⚠️ +24h</span>
                            @else
                                <span style="background: #ccfbf1; color: #0f766e; font-weight: 600; font-size: 12px; padding: 2px 10px; border-radius: 20px;">✅ OK</span>
                            @endif
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <a href="{{ route('lots.sortirTunnel', $lot->id) }}" onclick="return confirm('Confirmer la sortie de ce lot du tunnel ?');" style="background: #0284c7; color: white; padding: 6px 10px; border-radius: 8px; text-decoration: none; font-size: 12px; white-space: nowrap;">
                                🖨️ Bon de sortie
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">
                            Aucun lot en congélation.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
