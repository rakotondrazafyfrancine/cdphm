@extends('layouts.app')
@section('title', 'Sortie de lot - Chambre')

@section('content')
<div style="max-width: 500px; margin: 0 auto;">
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;"><x-lucide-thermometer-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Sortie de lot</h2>
        <p style="color: #64748b; font-size: 14px; margin: 0 0 20px 0;">{{ $lot->numero }} — {{ $lot->client->nom ?? '—' }} — {{ $lot->espece ?? '—' }}</p>

        <div style="background: #f0fdfa; border: 1px solid #0d9488; border-radius: 10px; padding: 14px; margin-bottom: 20px; text-align: center;">
            <div style="font-size: 13px; color: #64748b;">Poids restant en chambre</div>
            <div style="font-size: 24px; font-weight: 800; color: #0f766e;">{{ number_format($lot->poids_restant ?? $lot->poids_entree, 2) }} kg</div>
        </div>

        <form method="POST" action="{{ route('lots.demanderSortieChambre', $lot->id) }}">
            @csrf
            <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;">Poids à sortir maintenant (kg)</label>
            <input type="number" step="0.01" name="poids_sortie" required
                   max="{{ $lot->poids_restant ?? $lot->poids_entree }}"
                   style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; margin-bottom: 6px;">
            @error('poids_sortie') <small style="color: #dc2626;"><x-lucide-triangle-alert class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> {{ $message }}</small> @enderror

            <button type="submit" style="width: 100%; background: #0d9488; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 15px; margin-top: 12px;">
                <x-lucide-check class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Confirmer le poids
            </button>
        </form>
    </div>
</div>
@endsection
