@extends('layouts.app')
@section('title', 'Tunnels')

@section('content')

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
    <div style="font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 16px;">
        ❄️ Tunnels de congélation
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        @forelse ($tunnels as $tunnel)
            @php
                $chargeKg = \App\Models\Lot::where('tunnel_id', $tunnel->id)->where('statut', 'en_congelation')->sum('poids_entree');
                $capaciteKg = $tunnel->capacite_tonnes * 1000;
                $pourcentage = $capaciteKg > 0 ? round(($chargeKg / $capaciteKg) * 100) : 0;
                $pourcentage = min($pourcentage, 100);

                $alerte24h = \App\Models\Lot::where('tunnel_id', $tunnel->id)
                    ->where('statut', 'en_congelation')
                    ->where('created_at', '<', now()->subHours(24))
                    ->exists();
            @endphp
            <a href="{{ route('tunnels.show', $tunnel->id) }}" style="text-decoration: none; color: inherit;">
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; text-align: center; box-shadow: 0 1px 3px rgba(15,23,42,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(15,23,42,0.10)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(15,23,42,0.06)'">
                    <div style="font-size: 40px;">❄️</div>
                    <div style="font-size: 36px; font-weight: 700; color: #1e293b; margin-top: 8px;">{{ $pourcentage }}%</div>
                    <div style="font-size: 18px; font-weight: 600; color: #0f766e; margin-top: 6px;">{{ $tunnel->nom }}</div>

                    {{-- Barre de progression --}}
                    <div style="background: #e2e8f0; border-radius: 8px; height: 12px; margin-top: 16px; overflow: hidden;">
                        <div style="width: {{ $pourcentage }}%; height: 100%; background: {{ $pourcentage > 80 ? '#dc2626' : 'linear-gradient(135deg, #0d9488, #0284c7)' }}; border-radius: 8px;"></div>
                    </div>

                    <div style="margin-top: 16px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                        <span style="display: inline-block; padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; {{ $chargeKg > 0 ? 'background: #fef3c7; color: #b45309;' : 'background: #ccfbf1; color: #0f766e;' }}">
                            {{ $chargeKg > 0 ? 'Occupé' : 'Disponible' }}
                        </span>
                        @if($alerte24h)
                            <span style="display: inline-block; padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; background: #fee2e2; color: #b91c1c;">
                                ⚠️ +24h
                            </span>
                        @endif
                    </div>

                    <div style="font-size: 13px; color: #64748b; margin-top: 14px; font-weight: 500;">
                        {{ number_format($chargeKg, 0, ',', ' ') }} / {{ number_format($capaciteKg, 0, ',', ' ') }} kg
                    </div>
                </div>
            </a>
        @empty
            <p style="color: #64748b;">Aucun tunnel enregistré.</p>
        @endforelse
    </div>
</div>

@endsection
