<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProduit extends Model
{
    protected $fillable = ['client_id', 'nom'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
