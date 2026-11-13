@php
    $user = auth()->user();
    $date = now()->locale('fr')->translatedFormat('D j M');
    $aujourdhui = now()->locale('fr')->translatedFormat('l j F Y');
@endphp
<div class="cd-entete" x-data="{ heure: '' }"
     x-init="const t = () => heure = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}); t(); setInterval(t, 20000);
             /* le nom entre dans la zone cliquable du menu : le menu s'ouvre alors juste sous le nom */
             $nextTick(() => { const zone = document.querySelector('.fi-user-menu .fi-dropdown-trigger'); const nom = $el.querySelector('.cd-entete-profil'); if (zone && nom && !zone.contains(nom)) zone.appendChild(nom); })">

    <div class="cd-entete-date cd-sk-cible" title="{{ $aujourdhui }}">
        <span class="cd-entete-pastille"><x-filament::icon icon="lucide-calendar" class="h-4 w-4" /></span>
        <span class="cd-entete-textes">
            <span class="cd-entete-haut capitalize">{{ $date }}</span>
            <span class="cd-entete-bas" x-text="heure"></span>
        </span>
    </div>

    {{-- nom et rôle à droite de l'avatar ; un clic ouvre le menu du profil --}}
    <button type="button" class="cd-entete-profil cd-sk-cible" tabindex="-1">
        <span class="cd-profil-textes">
            <span class="cd-entete-haut">{{ $user?->nomComplet() }}</span>
            <span class="cd-entete-bas cd-role">{{ $user?->libelleRole() }}</span>
        </span>
        <x-filament::icon icon="lucide-chevron-down" class="cd-chevron" />
    </button>
</div>
