<?php

namespace App\Filament\Resources\ChambreFroides\Pages;

use App\Filament\Resources\ChambreFroides\ChambreFroideResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewChambreFroide extends ViewRecord
{
    protected static string $resource = ChambreFroideResource::class;

    public function getTitle(): string
    {
        return 'Chambre froide : ' . $this->record->nom;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retour')
                ->label('Retour')
                ->icon('lucide-arrow-left')
                ->color('gray')
                ->url(fn () => ChambreFroideResource::getUrl('index')),
        ];
    }
}
