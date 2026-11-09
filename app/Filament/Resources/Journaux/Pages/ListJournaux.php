<?php

namespace App\Filament\Resources\Journaux\Pages;

use App\Filament\Resources\Journaux\JournalResource;
use Filament\Resources\Pages\ListRecords;

class ListJournaux extends ListRecords
{
    protected static string $resource = JournalResource::class;
}
