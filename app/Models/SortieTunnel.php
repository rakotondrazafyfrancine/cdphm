<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SortieTunnel extends Model
{
    protected $table = 'sorties_tunnel';

    protected $fillable = ['lot_id', 'poids_sorti', 'heures', 'jours', 'montant', 'statut', 'tarif_applique', 'emplacement', 'date_entree', 'poids_enregistre', 'ecart', 'complement_de_id'];

    protected $casts = [
        'poids_sorti' => 'decimal:2',
        'poids_enregistre' => 'decimal:2',
        'ecart' => 'decimal:2',
        'heures'      => 'decimal:2',
        'montant'     => 'decimal:2',
        'date_entree' => 'datetime',
    ];

    /** Facture complémentaire (poids en plus constaté à la contre-pesée), s'il y en a une. */
    public function complement(): HasOne
    {
        return $this->hasOne(self::class, 'complement_de_id');
    }

    public function estComplement(): bool
    {
        return $this->complement_de_id !== null;
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
