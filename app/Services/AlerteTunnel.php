<?php

namespace App\Services;

use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Lot;
use App\Models\Tunnel;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

/**
 * Crée une notification (cloche de l'interface) quand un tunnel démarré depuis plus de 24 h peut être ouvert.
 * Les 24 h comptent à partir du démarrage du tunnel, pas de l'enregistrement des lots.
 * Une seule notification par tunnel, par cycle de congélation et par utilisateur.
 */
class AlerteTunnel
{
    public const TYPE = 'tunnel_ouvrable';

    /** @return int nombre de notifications créées */
    public static function verifier(): int
    {
        $tunnels = Tunnel::whereNotNull('demarre_le')
            ->where('demarre_le', '<=', now()->subHours(24))
            ->get();

        if ($tunnels->isEmpty()) {
            return 0;
        }

        $users = User::all();
        $creees = 0;

        foreach ($tunnels as $tunnel) {
            $lots = Lot::with('client')->where('tunnel_id', $tunnel->id)->where('statut', 'en_congelation')->get();
            if ($lots->isEmpty()) {
                continue;
            }

            foreach ($users as $user) {
                if (self::dejaNotifie($user, $tunnel)) {
                    continue;
                }

                $notification = Notification::make()
                    ->title($tunnel->nom . ' : 24 h écoulées, il peut être ouvert')
                    ->body(collect([
                        $lots->count() . ' lot' . ($lots->count() > 1 ? 's' : ''),
                        number_format((float) $lots->sum('poids_entree'), 0, ',', ' ') . ' kg',
                        'démarré le ' . $tunnel->demarre_le->format('d/m à H:i'),
                    ])->implode(' · '))
                    ->warning()
                    ->icon('lucide-snowflake')
                    ->viewData(['type' => self::TYPE, 'tunnel_id' => $tunnel->id, 'demarre' => $tunnel->demarre_le->timestamp])
                    ->actions([
                        Action::make('voir')
                            ->label('Voir le tunnel')
                            ->button()
                            ->url(TunnelResource::getUrl('view', ['record' => $tunnel->id]))
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

    private static function dejaNotifie(User $user, Tunnel $tunnel): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.viewData.type')) = ?", [self::TYPE])
            ->whereRaw("JSON_EXTRACT(data, '$.viewData.tunnel_id') = ?", [$tunnel->id])
            ->whereRaw("JSON_EXTRACT(data, '$.viewData.demarre') = ?", [$tunnel->demarre_le->timestamp])
            ->exists();
    }
}
