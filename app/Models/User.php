<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Override;

class User extends Authenticatable implements FilamentUser, HasName
{
    use Notifiable;

    protected $fillable = [
        'pseudo',
        'password',
        'role',
    ];
    public function getFilamentName(): string
    {
        return $this->pseudo;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    #[Override]
    public function getAuthIdentifierName()
    {
        return parent::getAuthIdentifierName();
        {
            return 'pseudo';
        }
    }
}
