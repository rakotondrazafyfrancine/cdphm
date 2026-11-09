@extends('layouts.app')

@section('title', 'Factures')

@section('content')
@php $th = 'padding:11px 20px;font-size:12px;font-weight:700;color:#000;text-transform:uppercase;'; @endphp
<div style="padding: 24px; max-width: 1200px; margin: 0 auto; color: #000;">

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; margin: 0; color: #000;">Factures</h1>
        <p style="color: #000; margin: 4px 0 0 0; font-size: 14px;">Suivi des paiements et sorties physiques</p>
    </div>

    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; color: #000;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="{{ $th }}">N° Lot</th>
                    <th style="{{ $th }}">Client</th>
                    <th style="{{ $th }}">Poids (kg)</th>
                    <th style="{{ $th }}">Montant (Ar)</th>
                    <th style="{{ $th }}">Statut</th>
                    <th style="{{ $th }}">Date</th>
                    <th style="{{ $th }}">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factures as $facture)
                @php
                    $badge = match($facture->statut) {
                        'en_attente_paiement' => ['#fef3c7', 'En attente de paiement'],
                        'paye' => ['#dbeafe', 'Payée — sortie à confirmer'],
                        'sortie_effectuee' => ['#dcfce7', 'Sortie effectuée'],
                        default => ['#f1f5f9', $facture->statut],
                    };
                    $btn = 'display:inline-block;box-sizing:border-box;min-width:120px;height:32px;line-height:30px;padding:0 14px;border:1px solid #cbd5e1;border-radius:8px;color:#000;font-size:13px;font-weight:600;text-align:center;text-decoration:none;cursor:pointer;';
                @endphp
                <tr style="border-top: 1px solid #f1f5f9;">
                    <td style="padding: 12px 20px; font-weight: 700; vertical-align: middle;">{{ $facture->lot->numero ?? '—' }}</td>
                    <td style="padding: 12px 20px; vertical-align: middle;">{{ $facture->lot->client->nom ?? '—' }}</td>
                    <td style="padding: 12px 20px; font-weight: 600; vertical-align: middle;">{{ number_format($facture->poids_sorti, 2) }}</td>
                    <td style="padding: 12px 20px; font-weight: 700; vertical-align: middle;">{{ number_format($facture->montant, 0, ',', ' ') }}</td>
                    <td style="padding: 12px 20px; vertical-align: middle;">
                        <span style="background: {{ $badge[0] }}; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap;">
                            {{ $badge[1] }}
                        </span>
                    </td>
                    <td style="padding: 12px 20px; font-size: 13px; vertical-align: middle;">{{ $facture->created_at->format('d/m/Y H:i') }}</td>
                    <td style="padding: 12px 20px; vertical-align: middle;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a href="{{ $facture->type === 'tunnel' ? route('sortiesTunnel.facture', $facture->id) : route('sortiesChambre.facture', $facture->id) }}" target="_blank" style="{{ $btn }}background:#f1f5f9;">
                                Facture
                            </a>
                            @if($facture->statut === 'en_attente_paiement')
                                <form method="POST" action="{{ $facture->type === 'tunnel' ? route('sortiesTunnel.payer', $facture->id) : route('sortiesChambre.payer', $facture->id) }}" style="margin:0;" onsubmit="return confirm('Confirmer le paiement de {{ number_format($facture->montant,0,',',' ') }} Ar ?')">
                                    @csrf
                                    <button type="submit" style="{{ $btn }}background:#dbeafe;">Marquer payée</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 32px; text-align: center;">Aucune facture enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
