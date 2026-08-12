@extends('layouts.app')
@section('title', 'Rapports')

@section('content')

<div style="margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #1e293b;">
        📊 Rapports — Comparaison des 2 derniers mois
    </h2>
    <p style="color: #64748b; margin-top: 4px; font-size: 13px;">
        {{ ucfirst($moisPrecedent->translatedFormat('F Y')) }} vs {{ ucfirst($moisActuel->translatedFormat('F Y')) }}
    </p>
</div>

{{-- ===== CARTES COMPARATIVES ===== --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">

    {{-- MOIS PRÉCÉDENT --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
        <div style="background: linear-gradient(135deg, #64748b, #475569); padding: 14px 20px;">
            <div style="color: white; font-size: 15px; font-weight: 600;">
                📅 {{ ucfirst($moisPrecedent->translatedFormat('F Y')) }}
            </div>
            <div style="color: rgba(255,255,255,0.7); font-size: 12px;">Mois précédent</div>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 22px; font-weight: 700; color: #1e293b;">{{ $statsPrecedent['nb_lots'] }}</div>
                    <div style="font-size: 12px; color: #64748b;">📦 Lots entrés</div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 22px; font-weight: 700; color: #1e293b;">{{ number_format($statsPrecedent['poids_total'], 0, ',', ' ') }} kg</div>
                    <div style="font-size: 12px; color: #64748b;">⚖️ Poids total</div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: #0284c7;">{{ number_format($statsPrecedent['recettes_tunnel'], 0, ',', ' ') }} Ar</div>
                    <div style="font-size: 12px; color: #64748b;">❄️ Recettes tunnel</div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: #0d9488;">{{ number_format($statsPrecedent['recettes_chambre'], 0, ',', ' ') }} Ar</div>
                    <div style="font-size: 12px; color: #64748b;">🧊 Recettes chambre</div>
                </div>
            </div>
            <div style="margin-top: 14px; background: #f0fdfa; border: 1px solid #0d9488; border-radius: 10px; padding: 14px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #0f766e;">{{ number_format($statsPrecedent['total'], 0, ',', ' ') }} Ar</div>
                <div style="font-size: 13px; color: #64748b;">💰 Total recettes</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px;">
                <div style="background: #ccfbf1; border-radius: 8px; padding: 10px; text-align: center;">
                    <div style="font-weight: 700; color: #0f766e;">{{ $statsPrecedent['lots_sortis'] }}</div>
                    <div style="font-size: 11px; color: #64748b;">✅ Lots sortis</div>
                </div>
                <div style="background: #fef3c7; border-radius: 8px; padding: 10px; text-align: center;">
                    <div style="font-weight: 700; color: #b45309;">{{ $statsPrecedent['lots_en_stock'] }}</div>
                    <div style="font-size: 11px; color: #64748b;">📦 En stock</div>
                </div>
            </div>
        </div>
    </div>

    {{-- MOIS ACTUEL --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
        <div style="background: linear-gradient(135deg, #0d9488, #0284c7); padding: 14px 20px;">
            <div style="color: white; font-size: 15px; font-weight: 600;">
                📅 {{ ucfirst($moisActuel->translatedFormat('F Y')) }}
            </div>
            <div style="color: rgba(255,255,255,0.7); font-size: 12px;">Mois actuel</div>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    @php $diffLots = $statsActuel['nb_lots'] - $statsPrecedent['nb_lots']; @endphp
                    <div style="font-size: 22px; font-weight: 700; color: #1e293b;">{{ $statsActuel['nb_lots'] }}</div>
                    <div style="font-size: 12px; color: #64748b;">📦 Lots entrés</div>
                    <div style="font-size: 11px; font-weight: 600; color: {{ $diffLots >= 0 ? '#0d9488' : '#dc2626' }}; margin-top: 4px;">
                        {{ $diffLots >= 0 ? '▲ +' : '▼ ' }}{{ $diffLots }}
                    </div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    @php $diffPoids = $statsActuel['poids_total'] - $statsPrecedent['poids_total']; @endphp
                    <div style="font-size: 22px; font-weight: 700; color: #1e293b;">{{ number_format($statsActuel['poids_total'], 0, ',', ' ') }} kg</div>
                    <div style="font-size: 12px; color: #64748b;">⚖️ Poids total</div>
                    <div style="font-size: 11px; font-weight: 600; color: {{ $diffPoids >= 0 ? '#0d9488' : '#dc2626' }}; margin-top: 4px;">
                        {{ $diffPoids >= 0 ? '▲ +' : '▼ ' }}{{ number_format($diffPoids, 0, ',', ' ') }} kg
                    </div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: #0284c7;">{{ number_format($statsActuel['recettes_tunnel'], 0, ',', ' ') }} Ar</div>
                    <div style="font-size: 12px; color: #64748b;">❄️ Recettes tunnel</div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 18px; font-weight: 700; color: #0d9488;">{{ number_format($statsActuel['recettes_chambre'], 0, ',', ' ') }} Ar</div>
                    <div style="font-size: 12px; color: #64748b;">🧊 Recettes chambre</div>
                </div>
            </div>
            <div style="margin-top: 14px; background: #f0fdfa; border: 1px solid #0d9488; border-radius: 10px; padding: 14px; text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #0f766e;">{{ number_format($statsActuel['total'], 0, ',', ' ') }} Ar</div>
                <div style="font-size: 13px; color: #64748b;">💰 Total recettes</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px;">
                <div style="background: #ccfbf1; border-radius: 8px; padding: 10px; text-align: center;">
                    <div style="font-weight: 700; color: #0f766e;">{{ $statsActuel['lots_sortis'] }}</div>
                    <div style="font-size: 11px; color: #64748b;">✅ Lots sortis</div>
                </div>
                <div style="background: #fef3c7; border-radius: 8px; padding: 10px; text-align: center;">
                    <div style="font-weight: 700; color: #b45309;">{{ $statsActuel['lots_en_stock'] }}</div>
                    <div style="font-size: 11px; color: #64748b;">📦 En stock</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== TABLEAU MOIS ACTUEL ===== --}}
<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.06); margin-bottom: 20px;">
    <div style="background: linear-gradient(135deg, #0d9488, #0284c7); padding: 14px 20px;">
        <div style="color: white; font-size: 15px; font-weight: 600;">
            📋 Détail des lots — {{ ucfirst($moisActuel->translatedFormat('F Y')) }}
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">N° Lot</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Client</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Produit</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Quantité (kg)</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Étape</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Tunnel (Ar)</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Chambre (Ar)</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Total (Ar)</th>
                    <th style="padding: 10px; color: #0f766e; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lotsActuel as $lot)
                    <tr style="border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.background='#f0fdfa'" onmouseout="this.style.background='white'">
                        <td style="padding: 10px; font-weight: 600; color: #0f766e;">{{ $lot->numero_lot }}</td>
                        <td style="padding: 10px;">{{ $lot->client->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ $lot->produit->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->quantite, 0, ',', ' ') }}</td>
                        <td style="padding: 10px;">
                            @php
                                $etapeLabel = match($lot->etape) {
                                    'congelation' => ['label' => 'Congélation', 'bg' => '#e0f2fe', 'color' => '#0369a1'],
                                    'stockage' => ['label' => 'Stockage', 'bg' => '#ccfbf1', 'color' => '#0f766e'],
                                    'sortie' => ['label' => 'Sortie', 'bg' => '#f1f5f9', 'color' => '#64748b'],
                                    default => ['label' => ucfirst($lot->etape), 'bg' => '#f1f5f9', 'color' => '#64748b'],
                                };
                            @endphp
                            <span style="background: {{ $etapeLabel['bg'] }}; color: {{ $etapeLabel['color'] }}; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">
                                {{ $etapeLabel['label'] }}
                            </span>
                        </td>
                        <td style="padding: 10px;">{{ number_format($lot->montant_tunnel ?? 0, 0, ',', ' ') }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->montant_chambre ?? 0, 0, ',', ' ') }}</td>
                        <td style="padding: 10px; font-weight: 600; color: #0f766e;">{{ number_format(($lot->montant_tunnel ?? 0) + ($lot->montant_chambre ?? 0), 0, ',', ' ') }}</td>
                        <td style="padding: 10px; color: #64748b;">{{ $lot->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #64748b;">
                            Aucun lot ce mois-ci.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== TABLEAU MOIS PRÉCÉDENT ===== --}}
<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
    <div style="background: linear-gradient(135deg, #64748b, #475569); padding: 14px 20px;">
        <div style="color: white; font-size: 15px; font-weight: 600;">
            📋 Détail des lots — {{ ucfirst($moisPrecedent->translatedFormat('F Y')) }}
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">N° Lot</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Client</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Produit</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Quantité (kg)</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Étape</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Tunnel (Ar)</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Chambre (Ar)</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Total (Ar)</th>
                    <th style="padding: 10px; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; text-align: left;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lotsPrecedent as $lot)
                    <tr style="border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 10px; font-weight: 600; color: #475569;">{{ $lot->numero_lot }}</td>
                        <td style="padding: 10px;">{{ $lot->client->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ $lot->produit->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->quantite, 0, ',', ' ') }}</td>
                        <td style="padding: 10px;">
                            @php
                                $etapeLabel = match($lot->etape) {
                                    'congelation' => ['label' => 'Congélation', 'bg' => '#e0f2fe', 'color' => '#0369a1'],
                                    'stockage' => ['label' => 'Stockage', 'bg' => '#ccfbf1', 'color' => '#0f766e'],
                                    'sortie' => ['label' => 'Sortie', 'bg' => '#f1f5f9', 'color' => '#64748b'],
                                    default => ['label' => ucfirst($lot->etape), 'bg' => '#f1f5f9', 'color' => '#64748b'],
                                };
                            @endphp
                            <span style="background: {{ $etapeLabel['bg'] }}; color: {{ $etapeLabel['color'] }}; padding: 3px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">
                                {{ $etapeLabel['label'] }}
                            </span>
                        </td>
                        <td style="padding: 10px;">{{ number_format($lot->montant_tunnel ?? 0, 0, ',', ' ') }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->montant_chambre ?? 0, 0, ',', ' ') }}</td>
                        <td style="padding: 10px; font-weight: 600; color: #475569;">{{ number_format(($lot->montant_tunnel ?? 0) + ($lot->montant_chambre ?? 0), 0, ',', ' ') }}</td>
                        <td style="padding: 10px; color: #64748b;">{{ $lot->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #64748b;">
                            Aucun lot ce mois-ci.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
