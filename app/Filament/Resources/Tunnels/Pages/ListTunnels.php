<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Tunnel;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListTunnels extends ListRecords
{
    protected static string $resource = TunnelResource::class;

    protected string $view = 'filament.pages.liste-cartes';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getCartes(): Collection
    {
        return Tunnel::orderBy('nom')->get();
    }

    public function getLargeurMinCarte(): int
    {
        return 320;
    }

    public function getTitreCartes(): string
    {
        return 'Tunnels de congélation';
    }

    public function getIconeCartes(): string
    {
        return 'lucide-snowflake';
    }

    public function getMessageVide(): string
    {
        return 'Aucun tunnel enregistré.';
    }

    /** Bouton « Démarrer » d'une carte : lance les 24 h de congélation du tunnel. */
    public function demarrerTunnel(int $id): void
    {
        $tunnel = Tunnel::findOrFail($id);

        if ($tunnel->estDemarre() || ! $tunnel->lots()->where('statut', 'en_congelation')->exists()) {
            Notification::make()->title($tunnel->nom . ' ne peut pas être démarré')
                ->body('Il est déjà démarré ou il ne contient aucun lot en congélation.')->warning()->send();

            return;
        }

        $tunnel->demarrer();

        Notification::make()->title($tunnel->nom . ' démarré')
            ->body('Compte à rebours de 24 h lancé : ouvrable à partir du ' . $tunnel->fresh()->ouvrableLe()->format('d/m à H:i') . '.')
            ->success()->send();
    }

    public function urlDetail(Tunnel $record): string
    {
        return TunnelResource::getUrl('view', ['record' => $record]);
    }
}
