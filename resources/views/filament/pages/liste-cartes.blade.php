<x-filament-panels::page>
    <div class="cd-panneau">
        <div class="cd-panneau-titre">
            <x-filament::icon :icon="$this->getIconeCartes()" class="h-5 w-5" />
            {{ $this->getTitreCartes() }}
        </div>

        <div class="cd-grille-cartes" style="--cd-min: {{ $this->getLargeurMinCarte() }}px">
            @forelse ($this->getCartes() as $record)
                @php
                    $estTunnel = $record instanceof \App\Models\Tunnel;
                    if ($estTunnel) {
                        $charge = (float) \App\Models\Lot::where('tunnel_id', $record->id)->where('statut', 'en_congelation')->sum('poids_entree');
                        $alerte = \App\Models\Lot::where('tunnel_id', $record->id)->where('statut', 'en_congelation')->where('created_at', '<', now()->subHours(24))->exists();
                    } else {
                        $charge = (float) \App\Models\Lot::where('chambre_id', $record->id)->where('statut', 'en_stock')->get()
                            ->sum(fn ($l) => $l->poids_restant ?? $l->poids_entree);
                        $alerte = false;
                    }
                    $capacite = (float) $record->capacite_tonnes * 1000;
                    $pct = $capacite > 0 ? min(100, (int) round($charge / $capacite * 100)) : 0;
                    $plein = $capacite > 0 && $charge >= $capacite;
                @endphp
                <a href="{{ $this->urlDetail($record) }}" wire:navigate class="cd-carte-lien">
                    <div class="cd-carte">
                        <div class="cd-carte-icone">
                            <x-filament::icon :icon="$estTunnel ? 'lucide-snowflake' : 'lucide-thermometer-snowflake'" class="h-8 w-8" />
                        </div>
                        <div class="cd-carte-pct">{{ $pct }}%</div>
                        <div class="cd-carte-nom">{{ $record->nom }}</div>
                        <div class="cd-carte-barre"><div style="width: {{ $pct }}%" class="{{ $pct > 80 ? 'cd-danger' : '' }}"></div></div>
                        <div class="cd-carte-badges">
                            <span class="cd-pastille {{ $plein ? 'cd-pastille-occupe' : 'cd-pastille-libre' }}">{{ $plein ? 'Occupé' : 'Disponible' }}</span>
                            @if ($alerte)
                                <span class="cd-pastille cd-pastille-alerte">+24 h</span>
                            @endif
                        </div>
                        <div class="cd-carte-kg">{{ number_format($charge, 0, ',', ' ') }} / {{ number_format($capacite, 0, ',', ' ') }} kg</div>
                    </div>
                </a>
            @empty
                <p class="cd-vide">{{ $this->getMessageVide() }}</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
