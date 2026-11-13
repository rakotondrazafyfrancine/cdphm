<?php

namespace App\Filament\Resources\Utilisateurs\Pages;

use App\Filament\Resources\Utilisateurs\UtilisateurResource;
use Filament\Resources\Pages\ManageRecords;

class GererUtilisateurs extends ManageRecords
{
    protected static string $resource = UtilisateurResource::class;

    protected ?string $heading = 'Utilisateurs';
}
