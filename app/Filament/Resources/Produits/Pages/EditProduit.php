<?php

namespace App\Filament\Resources\Produits\Pages;

use App\Filament\Resources\Produits\ProduitResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduit extends EditRecord
{
    protected static string $resource = ProduitResource::class;

    /** Sans nom, le produit prend le nom de sa catégorie (ex. « Langouste »). */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['nom'] ?? null)) {
            $data['nom'] = ucfirst(trim((string) $data['categorie']));
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    if ($this->record->lots()->exists()) {
                        Notification::make()->title('Suppression impossible')
                            ->body('Ce produit est utilisé dans des lots.')->danger()->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ProduitResource::getUrl('index');
    }
}
