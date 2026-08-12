<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStockage extends Model
{
    protected $fillable = ['lot_id', 'chambre_froide_id', 'date_entree', 'date_sortie', 'cout', 'paye', 'duree_indeterminee'];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
    public function chambreFroide()
    {
        return $this->belongsTo(ChambreFroide::class);
    }
}
