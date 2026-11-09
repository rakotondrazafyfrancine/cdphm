@php
    $sorties = $lot->sortiesChambre()->orderBy('id')->get();
    $statuts = ['en_attente_paiement' => 'En attente de paiement', 'paye' => 'Payée', 'sortie_effectuee' => 'Sortie effectuée'];
@endphp
<div class="space-y-3 text-sm">
    <div class="grid grid-cols-3 gap-3 text-center">
        <div class="rounded-lg bg-gray-50 p-2 dark:bg-white/5"><div class="text-xs text-gray-500">Poids entré</div><div class="font-bold">{{ number_format($lot->poids_entree, 2, ',', ' ') }} kg</div></div>
        <div class="rounded-lg bg-gray-50 p-2 dark:bg-white/5"><div class="text-xs text-gray-500">Déjà sorti</div><div class="font-bold">{{ number_format($lot->poids_entree - ($lot->poids_restant ?? $lot->poids_entree), 2, ',', ' ') }} kg</div></div>
        <div class="rounded-lg bg-gray-50 p-2 dark:bg-white/5"><div class="text-xs text-gray-500">Reste en chambre</div><div class="font-bold">{{ number_format($lot->poids_restant ?? $lot->poids_entree, 2, ',', ' ') }} kg</div></div>
    </div>

    @if ($sorties->isEmpty())
        <p class="py-4 text-center text-gray-500">Aucune sortie pour ce lot.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-white/10">
                        <th class="py-1.5 pe-3">Date</th><th class="pe-3 text-right">Poids sorti</th><th class="pe-3 text-right">Jours</th>
                        <th class="pe-3 text-right">Montant</th><th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sorties as $s)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pe-3">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-3 text-right font-semibold">{{ number_format($s->poids_sorti, 2, ',', ' ') }} kg</td>
                            <td class="pe-3 text-right">{{ $s->jours }}</td>
                            <td class="pe-3 text-right">{{ number_format($s->montant, 0, ',', ' ') }} Ar</td>
                            <td>{{ $statuts[$s->statut] ?? $s->statut }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
