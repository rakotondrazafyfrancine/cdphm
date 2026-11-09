@extends('layouts.app')
@section('title', 'Chambres')

@section('content')
<div class="container-fluid px-0">

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
        <div style="font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 16px;">
            <x-lucide-thermometer-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Chambres froides
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
            @forelse ($chambres as $chambre)
                @php
                    $chargeKg = \App\Models\Lot::where('chambre_id', $chambre->id)->where('statut', 'en_stock')->get()->sum(fn($lot) => $lot->poids_restant ?? $lot->poids_entree);
                    $capaciteKg = $chambre->capacite_tonnes * 1000;
                    $pourcentage = $capaciteKg > 0 ? round(($chargeKg / $capaciteKg) * 100) : 0;
                    $pourcentage = min($pourcentage, 100);
                @endphp
                <a href="{{ route('chambres.show', $chambre->id) }}" style="text-decoration: none; color: inherit;">
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px 12px; text-align: center; box-shadow: 0 1px 3px rgba(15,23,42,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(15,23,42,0.10)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(15,23,42,0.06)'">
                        <div style="font-size: 30px;"><x-lucide-thermometer-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /></div>
                        <div style="font-size: 26px; font-weight: 700; color: #1e293b; margin-top: 6px;">{{ $pourcentage }}%</div>
                        <div style="font-size: 15px; font-weight: 600; color: #0f766e; margin-top: 4px;">{{ $chambre->nom }}</div>

                        <div style="background: #e2e8f0; border-radius: 8px; height: 10px; margin-top: 12px; overflow: hidden;">
                            <div style="width: {{ $pourcentage }}%; height: 100%; background: {{ $pourcentage > 80 ? '#dc2626' : 'linear-gradient(135deg, #0d9488, #0284c7)' }}; border-radius: 8px;"></div>
                        </div>

                        <div style="margin-top: 12px;">
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; {{ $chargeKg > 0 ? 'background: #fef3c7; color: #b45309;' : 'background: #ccfbf1; color: #0f766e;' }}">
                                {{ $chargeKg > 0 ? 'Occupé' : 'Disponible' }}
                            </span>
                        </div>

                        <div style="font-size: 12px; color: #64748b; margin-top: 10px; font-weight: 500;">
                            {{ number_format($chargeKg, 0, ',', ' ') }} / {{ number_format($capaciteKg, 0, ',', ' ') }} kg
                        </div>
                    </div>
                </a>
            @empty
                <p style="color: #64748b;">Aucune chambre enregistrée.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
