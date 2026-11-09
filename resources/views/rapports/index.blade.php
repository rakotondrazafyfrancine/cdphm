@extends('layouts.app')

@section('content')
<div style="padding: 24px; max-width: 1300px; margin: 0 auto;">

    <!-- En-tête -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            @if(!empty(array_filter($filtres)))
            <a href="{{ route('rapports.index') }}" title="Retour au rapport complet"
               style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: #f1f5f9; color: #334155; text-decoration: none; font-size: 18px; transition: background 0.15s;"
               onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
               ←
            </a>
            @endif
            <div>
                <h1 style="font-size: 26px; font-weight: 800; margin: 0; color: #1e293b;"><x-lucide-chart-column class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Rapports</h1>
                <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Suivi des entrées, sorties et stock</p>
            </div>
        </div>
        <div style="position: relative;">
            <button onclick="document.getElementById('menuActions').classList.toggle('hidden')"
                    style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; padding: 11px 20px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; box-shadow: 0 2px 6px rgba(37,99,235,0.3);">
                Actions ▾
            </button>
            <div id="menuActions" class="hidden" style="position: absolute; right: 0; top: 48px; background: white; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.12); z-index: 10; min-width: 190px; overflow: hidden;">
                <a href="{{ route('rapports.imprimer') }}?{{ http_build_query($filtres) }}" target="_blank"
                   style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: #334155; border-bottom: 1px solid #f1f5f9; font-size: 14px;">
                   <x-lucide-printer class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Imprimer
                </a>
                <a href="{{ route('rapports.export-excel') }}?{{ http_build_query($filtres) }}"
                   style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: #334155; font-size: 14px;">
                   <x-lucide-package-plus class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Exporter Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Indicateurs -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe; border-radius: 14px; padding: 20px; text-align: center;">
            <div style="color: #2563eb; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;"><x-lucide-package-plus class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> ENTRÉES</div>
            <div style="font-size: 30px; font-weight: 800; color: #1e3a8a;">{{ $indicateurs['entrees'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #fecaca; border-radius: 14px; padding: 20px; text-align: center;">
            <div style="color: #dc2626; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;"><x-lucide-package-open class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> SORTIES</div>
            <div style="font-size: 30px; font-weight: 800; color: #7f1d1d;">{{ $indicateurs['sorties'] }}</div>
        </div>
       <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #fcd34d; border-radius: 14px; padding: 20px; text-align: center;">
    <div style="color: #b45309; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;"><x-lucide-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> EN TUNNEL</div>
    <div style="font-size: 22px; font-weight: 800; color: #78350f;">{{ $indicateurs['tunnel_lots'] }} lots — {{ number_format($indicateurs['tunnel_poids'], 0, ',', ' ') }} kg</div>
</div>
<div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; border-radius: 14px; padding: 20px; text-align: center;">
    <div style="color: #16a34a; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;"><x-lucide-thermometer-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> EN CHAMBRE</div>
    <div style="font-size: 22px; font-weight: 800; color: #14532d;">{{ $indicateurs['chambre_lots'] }} lots — {{ number_format($indicateurs['chambre_poids'], 0, ',', ' ') }} kg</div>
</div>
    </div>

    <!-- Filtres -->
    <form method="GET" action="{{ route('rapports.index') }}" style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 14px;">
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-calendar class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Date début (entrée)</label>
                <input type="date" name="date_debut" value="{{ $filtres['date_debut'] ?? '' }}"
                       style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-calendar class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Date fin (sortie)</label>
                <input type="date" name="date_fin" value="{{ $filtres['date_fin'] ?? '' }}"
                       style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
            </div>
            <div>
                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-clipboard-list class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Mouvement</label>
                <select name="mouvement" style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white;">
                    <option value="">Tous</option>
                    <option value="entree" {{ ($filtres['mouvement'] ?? '') === 'entree' ? 'selected' : '' }}>En stock</option>
                    <option value="sortie" {{ ($filtres['mouvement'] ?? '') === 'sortie' ? 'selected' : '' }}>Sorties</option>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px;">
                    <x-lucide-search class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Filtrer
                </button>
            </div>
        </div>

        <details style="border-top: 1px solid #f1f5f9; padding-top: 12px;">
            <summary style="cursor: pointer; color: #2563eb; font-size: 14px; font-weight: 600; list-style: none;">▸ Filtres avancés</summary>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-top: 14px;">
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-tag class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Produit</label>
                    <select name="espece" style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white;">
                        <option value="">Tous</option>
                        @foreach($especes as $espece)
                            <option value="{{ $espece }}" {{ ($filtres['espece'] ?? '') === $espece ? 'selected' : '' }}>{{ $espece }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-user-round class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Client</label>
                    <select name="client_id" style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white;">
                        <option value="">Tous</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ ($filtres['client_id'] ?? '') == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Tunnel</label>
                    <select name="tunnel_id" style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white;">
                        <option value="">Tous</option>
                        @foreach($tunnels as $tunnel)
                            <option value="{{ $tunnel->id }}" {{ ($filtres['tunnel_id'] ?? '') == $tunnel->id ? 'selected' : '' }}>{{ $tunnel->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: #334155;"><x-lucide-thermometer-snowflake class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Chambre</label>
                    <select name="chambre_id" style="width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white;">
                        <option value="">Toutes</option>
                        @foreach($chambres as $chambre)
                            <option value="{{ $chambre->id }}" {{ ($filtres['chambre_id'] ?? '') == $chambre->id ? 'selected' : '' }}>{{ $chambre->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </details>
    </form>

    <!-- Tableau -->
    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <x-lucide-clipboard-list class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Liste des mouvements
            <span style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 700; padding: 2px 10px; border-radius: 999px;">{{ $lots->count() }}</span>
        </div>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">N° Lot</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Client</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Produit</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Quantité (kg)</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Statut</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Date entrée</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Date sortie</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                <tr style="border-top: 1px solid #f1f5f9;">
                    <td style="padding: 12px 20px; font-weight: 700; color: #2563eb; font-size: 14px;">{{ $lot->numero }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155;">{{ $lot->client->nom ?? '—' }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155;">{{ $lot->espece ?? '—' }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155; font-weight: 600;">
                        {{ number_format($lot->poids_restant ?? $lot->poids_entree, 2) }}
                        @if($lot->poids_restant !== null && $lot->poids_restant != $lot->poids_entree)
                            <span style="font-size: 11px; color: #94a3b8; font-weight: 400;">/ {{ number_format($lot->poids_entree, 2) }} initial</span>
                        @endif
                    </td>
                    @php
                        $statutStyle = match($lot->statut) {
                            'sorti' => ['#fee2e2', '#dc2626', '● Sorti'],
                            'en_congelation' => ['#fef3c7', '#b45309', '● En tunnel'],
                            'en_stock' => ['#dcfce7', '#16a34a', '● En chambre'],
                            default => ['#f1f5f9', '#64748b', '● ' . ucfirst($lot->statut)],
                        };
                    @endphp
                    <td style="padding: 12px 20px;">
                        <span style="background: {{ $statutStyle[0] }}; color: {{ $statutStyle[1] }}; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700;">
                            {{ $statutStyle[2] }}
                        </span>
                    </td>
                    <td style="padding: 12px 20px; font-size: 13px; color: #64748b;">
                        {{ ($lot->date_entree ?? $lot->date_entree_chambre ?? $lot->created_at)?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td style="padding: 12px 20px; font-size: 13px; color: #64748b;">
                        {{ $lot->date_sortie?->format('d/m/Y') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 32px; text-align: center; color: #94a3b8; font-size: 14px;">Aucun mouvement trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Historique des sorties partielles (chambre) -->
    <details style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 24px;">
        <summary style="padding: 18px 20px; cursor: pointer; font-weight: 700; color: #1e293b; list-style: none;">
            <x-lucide-package-open class="lu" style="width:1em;height:1em;vertical-align:-.125em;" /> Historique des sorties de chambre
            <span style="background: #fef3c7; color: #b45309; font-size: 12px; font-weight: 700; padding: 2px 10px; border-radius: 999px; margin-left: 8px;">{{ $sortiesPartielles->count() }}</span>
        </summary>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">N° Lot</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Client</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Poids sorti (kg)</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Jours facturés</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Montant (Ar)</th>
                    <th style="padding: 11px 20px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sortiesPartielles as $sortie)
                <tr style="border-top: 1px solid #f1f5f9;">
                    <td style="padding: 12px 20px; font-weight: 700; color: #2563eb; font-size: 14px;">{{ $sortie->lot->numero ?? '—' }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155;">{{ $sortie->lot->client->nom ?? '—' }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155; font-weight: 600;">{{ number_format($sortie->poids_sorti, 2) }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155;">{{ $sortie->jours }}</td>
                    <td style="padding: 12px 20px; font-size: 14px; color: #334155; font-weight: 600;">{{ number_format($sortie->montant, 0, ',', ' ') }}</td>
                    <td style="padding: 12px 20px; font-size: 13px; color: #64748b;">{{ $sortie->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 32px; text-align: center; color: #94a3b8; font-size: 14px;">Aucune sortie de chambre enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </details>
</div>

<style>
.hidden { display: none; }
details > summary::-webkit-details-marker { display: none; }
</style>
@endsection
