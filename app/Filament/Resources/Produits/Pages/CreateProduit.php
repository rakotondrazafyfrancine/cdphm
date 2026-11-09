<?php

namespace App\Filament\Resources\Produits\Pages;

use App\Filament\Resources\Produits\ProduitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduit extends CreateRecord
{
    protected static string $resource = ProduitResource::class;

    /** Sans nom, le produit prend le nom de sa catégorie (ex. « Langouste »). */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['nom'] ?? null)) {
            $data['nom'] = ucfirst(trim((string) $data['categorie']));
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return ProduitResource::getUrl('index');
    }
}
