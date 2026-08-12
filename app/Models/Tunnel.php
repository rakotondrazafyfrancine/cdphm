<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tunnel extends Model
{
    protected $fillable = ['nom', 'capacite', 'statut'];

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }
}
