<?php

namespace App\Filament\Resources\ChambreFroides\Pages;

use App\Filament\Resources\ChambreFroides\ChambreFroideResource;
use App\Models\ChambreFroide;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListChambreFroides extends ListRecords
{
    protected static string $resource = ChambreFroideResource::class;

    protected string $view = 'filament.pages.liste-cartes';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getCartes(): Collection
    {
        return ChambreFroide::orderBy('nom')->get();
    }

    public function getLargeurMinCarte(): int
    {
        return 185;
    }

    public function getTitreCartes(): string
    {
        return 'Chambres froides';
    }

    public function getIconeCartes(): string
    {
        return 'lucide-thermometer-snowflake';
    }

    public function getMessageVide(): string
    {
        return 'Aucune chambre enregistrée.';
    }

    public function urlDetail(ChambreFroide $record): string
    {
        return ChambreFroideResource::getUrl('view', ['record' => $record]);
    }
}
