{{-- Session terminée (déconnexion, expiration) : on revient à la page de connexion au lieu d'afficher « Page expired ». --}}
<script data-navigate-once>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    window.location.href = @js(filament()->getLoginUrl());
                }
            });
        });
    });
</script>

{{-- Supprime l'ancien service worker (ancienne version de l'application) qui ralentissait chaque requête --}}
<script data-navigate-once>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((r) => r.unregister())).catch(() => {});
    }
</script>
