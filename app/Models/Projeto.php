<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projeto extends Model
{
    protected $fillable = ['titulo', 'descricao'];


    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class);
    }

    public function diagramas(): HasMany
    {
        return $this->hasMany(Diagrama::class);
    }
}
