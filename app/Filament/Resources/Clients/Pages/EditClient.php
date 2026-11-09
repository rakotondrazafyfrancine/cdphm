<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    if ($this->record->lots()->exists()) {
                        Notification::make()->title('Suppression impossible')
                            ->body('Ce client a des lots enregistrés : il ne peut pas être supprimé.')
                            ->danger()->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ClientResource::getUrl('index');
    }
}
