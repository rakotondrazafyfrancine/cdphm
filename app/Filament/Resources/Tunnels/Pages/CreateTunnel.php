<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\TunnelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTunnel extends CreateRecord
{
    protected static string $resource = TunnelResource::class;

    protected function getRedirectUrl(): string
    {
        return TunnelResource::getUrl('index');
    }
}
