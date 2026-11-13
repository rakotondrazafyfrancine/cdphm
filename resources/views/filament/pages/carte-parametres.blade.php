@php
    $nom = \App\Models\Parametre::lire('nom', 'CDPHM');
    $lignes = [
        ['icone' => 'lucide-map-pin', 'libelle' => 'Lieu', 'valeur' => \App\Models\Parametre::lire('lieu')],
        ['icone' => 'lucide-phone', 'libelle' => 'Contact', 'valeur' => \App\Models\Parametre::lire('contact')],
    ];
@endphp
<div class="cd-profil-carte">
    <div class="cd-profil-nom">{{ $nom }}</div>
    <div class="cd-profil-role">Établissement</div>

    <dl class="cd-profil-infos">
        @foreach ($lignes as $l)
            <div>
                <dt><x-filament::icon :icon="$l['icone']" class="h-4 w-4" /> {{ $l['libelle'] }}</dt>
                <dd>{{ filled($l['valeur']) ? $l['valeur'] : '—' }}</dd>
            </div>
        @endforeach
    </dl>
</div>
