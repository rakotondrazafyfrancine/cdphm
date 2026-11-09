<?php

namespace App\Filament\Resources\ChambreFroides\Pages;

use App\Filament\Resources\ChambreFroides\ChambreFroideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChambreFroide extends CreateRecord
{
    protected static string $resource = ChambreFroideResource::class;

    protected function getRedirectUrl(): string
    {
        return ChambreFroideResource::getUrl('index');
    }
}
