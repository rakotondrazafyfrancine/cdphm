{{-- Squelette de chargement : affiché sur le contenu dès qu'on clique sur un menu, retiré quand la nouvelle page est prête. --}}
<script data-navigate-once>
    (function () {
        const modele = () => {
            const bloc = (classe) => '<div class="cd-sq-bloc ' + classe + '"></div>';
            const lignes = Array.from({ length: 7 }, () => bloc('cd-sq-ligne')).join('');
            const cartes = Array.from({ length: 4 }, () => bloc('cd-sq-carte')).join('');
            return bloc('cd-sq-titre') + '<div class="cd-sq-cartes">' + cartes + '</div><div class="cd-sq-table">' + lignes + '</div>';
        };
        const retirer = () => document.querySelectorAll('.cd-sq').forEach((e) => e.remove());

        document.addEventListener('livewire:navigate', () => {
            const zone = document.querySelector('.fi-main');
            if (!zone || zone.querySelector('.cd-sq')) return;
            const sq = document.createElement('div');
            sq.className = 'cd-sq';
            sq.setAttribute('aria-hidden', 'true');
            sq.innerHTML = modele();
            zone.appendChild(sq);
        });
        document.addEventListener('livewire:navigated', retirer);
    })();
</script>
