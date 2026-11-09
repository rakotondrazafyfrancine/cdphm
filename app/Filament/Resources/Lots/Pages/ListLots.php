<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotResource;
use App\Filament\Concerns\ChangeVueListeGrille;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListLots extends ListRecords
{
    use ChangeVueListeGrille;

    protected static string $resource = LotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->actionVue(),
            Action::make('nouvelleEntree')
                ->label('Nouvelle entrée')
                ->icon('lucide-plus')
                ->url(fn () => \App\Filament\Resources\Lots\LotResource::getUrl('create')),
        ];
    }
}
