<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'pseudo',
        'password',
        'role',
    ];
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
