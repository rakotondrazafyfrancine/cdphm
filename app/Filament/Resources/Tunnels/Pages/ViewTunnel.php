<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\TunnelResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTunnel extends ViewRecord
{
    protected static string $resource = TunnelResource::class;

    public function getTitle(): string
    {
        return 'Tunnel : ' . $this->record->nom;
    }

    public function getSubheading(): ?string
    {
        return 'État : ' . $this->record->libelleEtat();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('demarrer')
                ->label('Démarrer le tunnel')
                ->icon('lucide-play')
                ->color('primary')
                ->visible(fn () => ! $this->record->estDemarre() && $this->record->lots()->where('statut', 'en_congelation')->exists())
                ->requiresConfirmation()
                ->modalHeading('Démarrer ' . $this->record->nom . ' ?')
                ->modalDescription('Les 24 h de congélation comptent à partir de maintenant pour tous les lots du tunnel. Une fois démarré, le tunnel ne peut plus recevoir de lot ni être ouvert avant 24 h.')
                ->modalSubmitActionLabel('Démarrer maintenant')
                ->action(function () {
                    $this->record->demarrer();
                    $this->redirect(TunnelResource::getUrl('view', ['record' => $this->record]));
                }),
            Action::make('retour')
                ->label('Retour')
                ->icon('lucide-arrow-left')
                ->color('gray')
                ->url(fn () => TunnelResource::getUrl('index')),
        ];
    }
}
