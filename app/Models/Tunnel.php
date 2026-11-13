<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tunnel extends Model
{
    protected $fillable = ['nom', 'capacite_tonnes', 'demarre_le'];

    protected $casts = [
        'demarre_le' => 'datetime',
    ];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    /** Un tunnel est « démarré » quand il a été lancé : il ne peut plus être ouvert avant 24 h. */
    public function estDemarre(): bool
    {
        return $this->demarre_le !== null;
    }

    public function ouvrableLe(): ?Carbon
    {
        return $this->demarre_le?->copy()->addHours(24);
    }

    /** Vrai tant que le tunnel est en chargement, ou une fois les 24 h écoulées. */
    public function peutEtreOuvert(): bool
    {
        return ! $this->estDemarre() || now()->gte($this->ouvrableLe());
    }

    /** @return 'chargement'|'ferme'|'pret' */
    public function etat(): string
    {
        if (! $this->estDemarre()) {
            return 'chargement';
        }

        return $this->peutEtreOuvert() ? 'pret' : 'ferme';
    }

    public function libelleEtat(): string
    {
        return match ($this->etat()) {
            'chargement' => 'En chargement (pas encore démarré)',
            'ferme' => 'Démarré le ' . $this->demarre_le->format('d/m H:i') . ' · ouvrable à partir du ' . $this->ouvrableLe()->format('d/m H:i'),
            'pret' => 'Prêt à ouvrir (24 h écoulées depuis le ' . $this->demarre_le->format('d/m H:i') . ')',
        };
    }

    public function demarrer(): void
    {
        $this->update(['demarre_le' => now()]);
    }

    /** Quand plus aucun lot n'est en congélation, le tunnel repasse en chargement pour le prochain cycle. */
    public function libererSiVide(): void
    {
        if ($this->estDemarre() && ! $this->lots()->where('statut', 'en_congelation')->exists()) {
            $this->update(['demarre_le' => null]);
        }
    }
}
