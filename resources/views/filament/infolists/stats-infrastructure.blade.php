@php
    $estTunnel = $record instanceof \App\Models\Tunnel;
    if ($estTunnel) {
        $charge = (float) \App\Models\Lot::where('tunnel_id', $record->id)->where('statut', 'en_congelation')->sum('poids_entree');
    } else {
        $charge = (float) \App\Models\Lot::where('chambre_id', $record->id)->where('statut', '!=', 'sorti')->get()
            ->sum(fn ($l) => $l->poids_restant ?? $l->poids_entree);
    }
    $capacite = (float) $record->capacite_tonnes * 1000;
    $pct = $capacite > 0 ? min(100, round($charge / $capacite * 100, 1)) : 0;
    $cartes = [
        ['Taux de remplissage', $pct . ' %', 'lucide-trending-up', true],
        ['Poids actuel', number_format($charge, 0, ',', ' ') . ' kg', 'lucide-scale', false],
        ['Capacité totale', number_format($capacite, 0, ',', ' ') . ' kg', 'lucide-ruler', false],
    ];
@endphp
<div class="cd-stats">
    @foreach ($cartes as [$libelle, $valeur, $icone, $accent])
        <div class="cd-stat {{ $accent ? 'cd-stat-accent' : '' }}">
            <x-filament::icon :icon="$icone" class="h-6 w-6" />
            <div class="cd-stat-valeur">{{ $valeur }}</div>
            <div class="cd-stat-libelle">{{ $libelle }}</div>
        </div>
    @endforeach
</div>
