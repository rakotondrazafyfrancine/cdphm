@extends('layouts.app')
@section('title', 'Détail Chambre Froide')

@section('content')

@if(session('success'))
    <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px;">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px;">
        ⚠️ {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px;">
        ⚠️
        @foreach ($errors->all() as $error)
              {{ $error }}<br>

        @endforeach
    </div>
@endif


<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(15,23,42,0.06);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
        <div style="font-size: 17px; font-weight: 600; color: #1e293b;">
            🧊 {{ $chambre->nom }}
        </div>
        <a href="{{ route('chambres.index') }}" style="background: #f1f5f9; color: #1e293b; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 13px;">
            ← Retour
        </a>
    </div>

    @php
        $chargeKg = $lots->where('statut', '!=', 'sorti')->sum(function($lot) {
             return $lot->poids_sortie ?? $lot->poids_entree;
        });
        $capaciteKg = $chambre->capacite_tonnes * 1000;
        $pourcentage = $capaciteKg > 0 ? round(($chargeKg / $capaciteKg) * 100) : 0;
        $pourcentage = min($pourcentage, 100);
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px;">
        <div style="background: #f0fdfa; border: 1px solid #0d9488; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 22px;">📦</div>
            <div style="font-size: 22px; font-weight: 700;">{{ $pourcentage }}%</div>
            <div style="font-size: 13px; color: #64748b;">Taux de remplissage</div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center;">
            <div style="font-size: 22px;">⚖️</div>
            <div style="font-size: 22px; font-weight: 700;">{{ number_format($chargeKg, 0, ',', ' ') }} kg</div>
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
        📋 Lots dans cette chambre
    </div>

    <!-- Assigner un lot du tunnel + Ajouter un nouveau lot -->
    <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">

        <!-- Formulaire pour assigner un lot existant du tunnel -->
         <form action="/chambres/{{ $chambre->id }}/assigner-lot" method="POST" id="formAssigner" style="flex: 1; min-width: 280px; display: flex; gap: 8px; background: #f0fdfa; border: 1px solid #0d9488; border-radius: 12px; padding: 12px;">
            @csrf
            <select id="selectLot" name="lot_id" style="flex: 1; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px;">
                <option value="">🔄 Assigner un lot du tunnel...</option>
                @forelse ($lotsDisponibles as $lotDispo)
                    <option value="{{ $lotDispo->id }}">
                        {{ $lotDispo->numero }} — {{ $lotDispo->client->nom ?? '—' }} — {{ $lotDispo->tunnel->nom ?? '—' }} ({{ number_format($lotDispo->poids_entree, 0, ',', ' ') }} kg)
                    </option>
                @empty
                    <option value="" disabled>Aucun lot disponible en tunnel</option>
                @endforelse
            </select>
            <button type="submit" style="background: #0d9488; color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer;">
                ✔ Assigner
            </button>
        </form>

        <!-- Bouton pour créer un nouveau lot -->
        <a href="{{ route('lots.create', ['chambre_id' => $chambre->id]) }}" style="text-decoration: none; flex: 1; min-width: 200px;">
            <div style="border: 2px dashed #0d9488; border-radius: 12px; padding: 16px; text-align: center; color: #0d9488; font-weight: 600; height: 100%; box-sizing: border-box;">
                ➕ Ajouter un lot
            </div>
        </a>

    </div>

    <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #0d9488, #0284c7);">
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">N° Lot</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Client</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Produit</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Poids Tunnel (kg)</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Contre-Pesée (kg)</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Date d'entrée</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Depuis</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Impression</th>
                    <th style="padding: 12px 10px; color: white; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lots as $lot)
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 10px;"><strong>{{ $lot->numero }}</strong></td>
                        <td style="padding: 10px;">{{ $lot->client->nom ?? '—' }}</td>
                        <td style="padding: 10px;">{{ $lot->espece ?? '—' }}</td>
                        <td style="padding: 10px;">{{ number_format($lot->poids_entree, 0, ',', ' ') }}</td>
                        <td style="padding: 10px;">
                            @if($lot->statut != 'sorti')
                                <form action="{{ route('lots.majContrePesee', $lot->id) }}" method="POST" style="display: flex; gap: 4px;">
                                    @csrf
                                    <input type="number" name="contre_pesee" value="{{ $lot->poids_sortie }}" style="width: 70px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 6px;" step="0.01">
                                    <button type="submit" style="background: #0d9488; color: white; border: none; border-radius: 6px; padding: 4px 8px; cursor: pointer;">✔</button>
                                </form>
                            @else
                                {{ number_format($lot->poids_sortie, 0, ',', ' ') }}
                            @endif
                        </td>
                        <td style="padding: 10px;">{{ $lot->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 10px;">{{ $lot->created_at->diffForHumans() }}</td>
                        <td style="padding: 10px;">
                            <a href="{{ route('lots.print', $lot->id) }}" target="_blank" style="background: #0284c7; color: white; padding: 6px 10px; border-radius: 8px; text-decoration: none; font-size: 12px; white-space: nowrap;">
                                🖨️ Bon d'entrée
                            </a>
                        </td>
                        <td style="padding: 10px;">
                            <form action="{{ route('lots.destroy', $lot->id) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce lot ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: #dc2626; color: white; border: none; border-radius: 8px; padding: 6px 10px; font-size: 12px; cursor: pointer;">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #64748b;">
                            Aucun lot dans cette chambre pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
