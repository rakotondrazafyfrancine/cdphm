<?php

namespace App\Filament\Resources\Produits\Pages;

use App\Filament\Resources\Produits\ProduitResource;
use Filament\Actions\CreateAction;
use App\Filament\Concerns\ChangeVueListeGrille;
use Filament\Resources\Pages\ListRecords;

class ListProduits extends ListRecords
{
    use ChangeVueListeGrille;

    protected static string $resource = ProduitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->actionVue(),
            CreateAction::make(),
        ];
    }
}
