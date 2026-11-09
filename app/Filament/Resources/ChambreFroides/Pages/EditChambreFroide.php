<?php

namespace App\Filament\Resources\ChambreFroides\Pages;

use App\Filament\Resources\ChambreFroides\ChambreFroideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChambreFroide extends EditRecord
{
    protected static string $resource = ChambreFroideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ChambreFroideResource::getUrl('index');
    }
}
