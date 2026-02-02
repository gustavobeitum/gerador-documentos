<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projeto extends Model
{
    // PHP 8.4: Propriedade protegida para preenchimento em massa
    protected $fillable = ['titulo', 'descricao'];

    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class);
    }
}
