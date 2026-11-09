<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return 'Client : ' . $this->record->nom;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Modifier')->icon('lucide-pencil'),
            Action::make('retour')
                ->label('Retour')
                ->icon('lucide-arrow-left')
                ->color('gray')
                ->url(fn () => ClientResource::getUrl('index')),
        ];
    }
}
