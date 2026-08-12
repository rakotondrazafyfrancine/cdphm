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
        'capacite',
        'statut',        // disponible, pleine, maintenance
        'description'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'capacite' => 'decimal:2',
    ];

    /**
     * Relation avec les lots stockés dans cette chambre.
     */
    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class, 'chambre_id');
    }

    /**
     * Vérifier si la chambre est disponible.
     */
    public function getEstDisponibleAttribute(): bool
    {
        return $this->statut === 'disponible';
    }

    /**
     * Obtenir le taux d'occupation de la chambre.
     */
    public function getTauxOccupationAttribute(): float
    {
        $poidsStocke = $this->lots()->where('statut', 'en_stock')->sum('poids_entree');
        if ($this->capacite > 0) {
            return round(($poidsStocke / $this->capacite) * 100, 2);
        }
        return 0;
    }

    /**
     * Scope : Chambres disponibles.
     */
    public function scopeDisponible($query)
    {
        return $query->where('statut', 'disponible');
    }

    /**
     * Scope : Chambres avec capacité restante.
     */
    public function scopeAvecCapacite($query, $poidsRequis)
    {
        return $query->where('capacite', '>=', $poidsRequis);
    }
}
