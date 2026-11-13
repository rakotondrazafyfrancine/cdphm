@php
    $u = auth()->user();
    $lignes = [
        ['icone' => 'lucide-badge-check', 'libelle' => 'N° IM', 'valeur' => $u->matricule],
        ['icone' => 'lucide-building-2', 'libelle' => 'Département', 'valeur' => $u->departement],
        ['icone' => 'lucide-briefcase', 'libelle' => 'Fonction', 'valeur' => $u->fonction],
        ['icone' => 'lucide-at-sign', 'libelle' => 'Pseudo', 'valeur' => $u->pseudo],
    ];
@endphp
<div class="cd-profil-carte">
    <div class="cd-profil-nom">{{ $u->nomComplet() }}</div>
    <div class="cd-profil-role">{{ $u->libelleRole() }}</div>

    <dl class="cd-profil-infos">
        @foreach ($lignes as $l)
            <div>
                <dt><x-filament::icon :icon="$l['icone']" class="h-4 w-4" /> {{ $l['libelle'] }}</dt>
                <dd>{{ filled($l['valeur']) ? $l['valeur'] : '—' }}</dd>
            </div>
        @endforeach
    </dl>
</div>
