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
        'nom',
        'prenom',
        'matricule',
        'departement',
        'fonction',
        'photo',
    ];

    /** Rôles existants en base (la valeur « magasiner » est celle de l'énumération d'origine). */
    public const ROLES = [
        'receptionniste' => 'Réceptionniste',
        'magasiner' => 'Magasinier',
        'logistique' => 'Logistique',
        'comptable' => 'Comptable / caisse',
        'directeur' => 'Directeur',
    ];

    /** « Jean ANDRIANJANAKA » si la fiche est remplie, sinon le pseudo. */
    public function nomComplet(): string
    {
        $nom = trim(($this->prenom ?? '') . ' ' . mb_strtoupper((string) $this->nom));

        return $nom !== '' ? $nom : (string) $this->pseudo;
    }

    /** Adresse de la photo de profil, ou null si l'agent n'en a pas. */
    public function photoUrl(): ?string
    {
        return $this->photo ? '/storage/' . ltrim($this->photo, '/') : null;
    }

    public function libelleRole(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }

    public function getFilamentName(): string
    {
        return $this->nomComplet();
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
