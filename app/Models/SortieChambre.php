<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SortieChambre extends Model
{
    protected $table = 'sorties_chambre';

    protected $fillable = ['lot_id', 'poids_sorti', 'jours', 'montant', 'statut', 'tarif_applique', 'emplacement', 'date_entree', 'reste_apres'];

    protected $casts = [
        'poids_sorti' => 'decimal:2',
        'montant'     => 'decimal:2',
        'date_entree' => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
