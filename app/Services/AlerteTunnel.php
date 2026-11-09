<?php

namespace App\Services;

use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Lot;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

/**
 * Crée une notification (cloche de l'interface) pour chaque lot resté plus de 24 h en tunnel.
 * Une seule notification par lot et par utilisateur.
 */
class AlerteTunnel
{
    public const TYPE = 'tunnel_24h';

    /** @return int nombre de notifications créées */
    public static function verifier(): int
    {
        $lots = Lot::with(['client', 'tunnel'])
            ->where('statut', 'en_congelation')
            ->whereNotNull('tunnel_id')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        if ($lots->isEmpty()) {
            return 0;
        }

        $users = User::all();
        $creees = 0;

        foreach ($lots as $lot) {
            foreach ($users as $user) {
                if (self::dejaNotifie($user, $lot)) {
                    continue;
                }

                $heures = (int) $lot->created_at->diffInHours(now());
                $duree = $heures >= 48 ? intdiv($heures, 24) . ' jours' : $heures . ' h';

                $notification = Notification::make()
                    ->title($lot->numero . ' : plus de 24 h en tunnel')
                    ->body(collect([
                        $lot->client?->nom,
                        $lot->espece,
                        number_format((float) $lot->poids_entree, 0, ',', ' ') . ' kg',
                        $lot->tunnel?->nom,
                        'depuis ' . $duree,
                    ])->filter()->implode(' · '))
                    ->warning()
                    ->icon('lucide-triangle-alert')
                    ->viewData(['type' => self::TYPE, 'lot_id' => $lot->id])
                    ->actions([
                        Action::make('voir')
                            ->label('Voir le tunnel')
                            ->button()
                            ->url(TunnelResource::getUrl('view', ['record' => $lot->tunnel_id]))
                            ->markAsRead(),
                    ])
                    ->toDatabase();

                // notifyNow : enregistrement immédiat, sans file d'attente (aucun worker ne tourne)
                $user->notifyNow($notification);

                $creees++;
            }
        }

        return $creees;
    }

    private static function dejaNotifie(User $user, Lot $lot): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.viewData.type')) = ?", [self::TYPE])
            ->whereRaw("JSON_EXTRACT(data, '$.viewData.lot_id') = ?", [$lot->id])
            ->exists();
    }
}
