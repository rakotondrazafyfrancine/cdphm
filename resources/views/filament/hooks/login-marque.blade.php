@php
    $societe = \App\Models\Parametre::lire('nom', 'CDPHM');
    $logo = \App\Models\Parametre::lire('logo');
@endphp
<aside class="cd-marque">
    {{-- décor : bulles, poisson et vagues --}}
    <div class="cd-marque-decor" aria-hidden="true">
        <x-filament::icon icon="lucide-fish" class="cd-marque-poisson" />
        @foreach ([['6%', '1.1rem', '0s', '11s'], ['14%', '.6rem', '3s', '9s'], ['27%', '1.6rem', '6s', '14s'], ['41%', '.8rem', '1s', '10s'], ['58%', '1.3rem', '4s', '13s'], ['73%', '.7rem', '7s', '9s'], ['86%', '1.2rem', '2s', '12s']] as [$gauche, $taille, $delai, $duree])
            <span class="cd-bulle" style="left: {{ $gauche }}; width: {{ $taille }}; height: {{ $taille }}; animation-delay: {{ $delai }}; animation-duration: {{ $duree }};"></span>
        @endforeach
        <div class="cd-vague cd-vague-1"></div>
        <div class="cd-vague cd-vague-2"></div>
        <div class="cd-vague cd-vague-3"></div>
    </div>

    {{-- en tête du fond : le nom et sa signification --}}
    <header class="cd-marque-tete">
        @if ($logo)
            <img class="cd-marque-logo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="{{ $societe }}">
        @endif
        <p class="cd-marque-societe">{{ $societe }}</p>
        @if (strcasecmp($societe, 'CDPHM') === 0)
            <p class="cd-marque-texte">Centre de Distribution des Produits Halieutiques de Mahajanga</p>
        @endif
    </header>

    <div class="cd-marque-contenu">
        <h2 class="cd-marque-titre">
            <span>Gestion</span>
            <span>des stocks</span>
        </h2>
        <span class="cd-marque-trait"></span>
    </div>

    <p class="cd-marque-signature">@francine</p>
</aside>
