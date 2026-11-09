<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SortieTunnel extends Model
{
    protected $table = 'sorties_tunnel';

    protected $fillable = ['lot_id', 'poids_sorti', 'heures', 'jours', 'montant', 'statut', 'tarif_applique', 'emplacement', 'date_entree'];

    protected $casts = [
        'poids_sorti' => 'decimal:2',
        'heures'      => 'decimal:2',
        'montant'     => 'decimal:2',
        'date_entree' => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
