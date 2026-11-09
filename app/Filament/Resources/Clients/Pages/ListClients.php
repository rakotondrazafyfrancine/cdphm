<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use App\Filament\Concerns\ChangeVueListeGrille;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    use ChangeVueListeGrille;

    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->actionVue(),
            CreateAction::make(),
        ];
    }
}
