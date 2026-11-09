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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retour')
                ->label('Retour')
                ->icon('lucide-arrow-left')
                ->color('gray')
                ->url(fn () => TunnelResource::getUrl('index')),
        ];
    }
}
