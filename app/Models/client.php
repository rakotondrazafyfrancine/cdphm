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
}
