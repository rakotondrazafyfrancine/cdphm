@php
    $user = auth()->user();
    $date = now()->locale('fr')->translatedFormat('D j M');
    $aujourdhui = now()->locale('fr')->translatedFormat('l j F Y');
@endphp
<div class="cd-entete" x-data="{ heure: '' }"
     x-init="const t = () => heure = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}); t(); setInterval(t, 20000)">

    <div class="cd-entete-date cd-sk-cible" title="{{ $aujourdhui }}">
        <span class="cd-entete-pastille"><x-filament::icon icon="lucide-calendar" class="h-4 w-4" /></span>
        <span class="cd-entete-textes">
            <span class="cd-entete-haut capitalize">{{ $date }}</span>
            <span class="cd-entete-bas" x-text="heure"></span>
        </span>
    </div>

    {{-- nom et rôle à droite de l'avatar ; un clic ouvre le menu du profil --}}
    <button type="button" class="cd-entete-profil cd-sk-cible" x-on:click="document.querySelector('.fi-user-menu-trigger')?.click()">
        <span class="cd-profil-textes">
            <span class="cd-entete-haut">{{ $user?->pseudo }}</span>
        </span>
        <x-filament::icon icon="lucide-chevron-down" class="cd-chevron" />
    </button>
</div>
