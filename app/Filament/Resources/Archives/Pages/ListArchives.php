<?php

namespace App\Filament\Resources\Archives\Pages;

use App\Filament\Resources\Archives\ArchiveResource;
use Filament\Resources\Pages\ListRecords;

class ListArchives extends ListRecords
{
    protected static string $resource = ArchiveResource::class;

    protected static ?string $title = 'Archives des documents';
}
