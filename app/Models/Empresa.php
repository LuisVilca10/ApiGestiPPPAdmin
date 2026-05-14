<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ruc',
        'name_empresa',
        'name_represent',
        'lastname_represent',
        'trate_represent',
        'phone_represent',
        'departamento',
        'provincia',
        'distrito',
    ];

    public function practices()
    {
        return $this->hasMany(Practice::class);
    }
}
