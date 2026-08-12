<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCongelation extends Model
{
    protected $fillable = ['lot_id', 'tunnel_id', 'date_debut', 'date_fin', 'cout', 'penalite', 'paye', 'responsabilite_penalite'];

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
    public function tunnel()
    {
        return $this->belongsTo(Tunnel::class);
    }
}
