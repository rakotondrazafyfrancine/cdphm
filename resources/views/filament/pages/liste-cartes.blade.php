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
                        $etatTunnel = $record->etat();
                        $alerte = $etatTunnel === 'pret' && $charge > 0;
                    } else {
                        $charge = (float) \App\Models\Lot::where('chambre_id', $record->id)->where('statut', 'en_stock')->get()
                            ->sum(fn ($l) => $l->poids_restant ?? $l->poids_entree);
                        $alerte = false;
                    }
                    $capacite = (float) $record->capacite_tonnes * 1000;
                    $pct = $capacite > 0 ? min(100, (int) round($charge / $capacite * 100)) : 0;
                    $plein = $capacite > 0 && $charge >= $capacite;
                @endphp
                <div class="cd-carte-bloc">
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
                            @if ($estTunnel && $charge > 0)
                                @if ($alerte)
                                    <span class="cd-pastille cd-pastille-alerte">Prêt à ouvrir</span>
                                @elseif ($etatTunnel === 'ferme')
                                    <span class="cd-pastille cd-pastille-occupe"
                                          x-data="{ fin: {{ $record->ouvrableLe()->timestamp * 1000 }}, txt: '' }"
                                          x-init="const m = () => { const r = fin - Date.now(); txt = r <= 0 ? 'Prêt à ouvrir' : 'Ouvrable dans ' + Math.floor(r / 3600000) + ' h ' + String(Math.floor(r % 3600000 / 60000)).padStart(2, '0') + ' min'; }; m(); setInterval(m, 30000)"
                                          x-text="txt">Démarré · ouvrable {{ $record->ouvrableLe()->format('d/m H:i') }}</span>
                                @else
                                    <span class="cd-pastille">En chargement</span>
                                @endif
                            @endif
                        </div>
                        <div class="cd-carte-kg">{{ number_format($charge, 0, ',', ' ') }} / {{ number_format($capacite, 0, ',', ' ') }} kg</div>
                    </div>
                </a>
                @if ($estTunnel && $etatTunnel === 'chargement' && $charge > 0)
                    <button type="button" class="cd-bouton-demarrer"
                            wire:click="demarrerTunnel({{ $record->id }})"
                            wire:confirm="Démarrer {{ $record->nom }} ? Le compte à rebours de 24 h commence maintenant : le tunnel ne pourra plus recevoir de lot ni être ouvert avant 24 h.">
                        <x-filament::icon icon="lucide-play" class="h-4 w-4" /> Démarrer le tunnel
                    </button>
                @endif
                </div>
            @empty
                <p class="cd-vide">{{ $this->getMessageVide() }}</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
