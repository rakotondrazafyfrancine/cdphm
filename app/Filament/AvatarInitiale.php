<?php

namespace App\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/** Avatar rond avec l'initiale du pseudo, aux couleurs de l'interface (sans service externe). */
class AvatarInitiale implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        if (method_exists($record, 'photoUrl') && ($photo = $record->photoUrl())) {
            return $photo;
        }

        $initiale = mb_strtoupper(mb_substr((string) ($record->prenom ?: $record->pseudo ?: '?'), 0, 1));

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            . '<rect width="64" height="64" fill="#336399"/>'
            . '<text x="32" y="43" font-family="Arial, sans-serif" font-size="30" font-weight="700" text-anchor="middle" fill="#ffffff">' . e($initiale) . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
