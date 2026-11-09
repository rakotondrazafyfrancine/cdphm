<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChambreFroide extends Model
{
    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'nom',
        'capacite_tonnes',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'capacite_tonnes' => 'decimal:2',
    ];

    /**
     * Relation avec les lots stockés dans cette chambre.
     */
    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class, 'chambre_id');
    }

    /**
     * Poids réellement stocké (kg) : poids restant, sorties partielles déduites.
     */
    public function getPoidsStockeAttribute(): float
    {
        return (float) $this->lots()
            ->where('statut', 'en_stock')
            ->get()
            ->sum(fn($lot) => $lot->poids_restant ?? $lot->poids_entree);
    }

    /**
     * Capacité de la chambre en kg (capacite_tonnes si présente, sinon capacite).
     */
    public function getCapaciteKgAttribute(): float
    {
        return (float) ($this->attributes['capacite_tonnes'] ?? 0) * 1000;
    }

    /**
     * Obtenir le taux d'occupation de la chambre.
     */
    public function getTauxOccupationAttribute(): float
    {
        $capaciteKg = $this->capacite_kg;

        if ($capaciteKg > 0) {
            return round(($this->poids_stocke / $capaciteKg) * 100, 2);
        }

        return 0;
    }

}
