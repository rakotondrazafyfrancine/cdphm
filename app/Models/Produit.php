<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $fillable = ['nom', 'categorie'];
    public function lots()
    {
        return $this->hasMany(Lot::class);
    }
}
