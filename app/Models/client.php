<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['nom', 'contact', 'adresse', 'type'];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function produits()
    {
        return $this->hasMany(ClientProduit::class)->orderBy('nom');
    }

    /**
     * Tous les produits que ce client apporte : sa fiche produits + ceux de ses lots.
     *
     * @return array<int, string>
     */
    public function produitsApportes(): array
    {
        return $this->produits->pluck('nom')
            ->merge($this->lots->pluck('espece'))
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
