<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Tunnel;
use Filament\Actions\CreateAction;
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

    public function urlDetail(Tunnel $record): string
    {
        return TunnelResource::getUrl('view', ['record' => $record]);
    }
}
