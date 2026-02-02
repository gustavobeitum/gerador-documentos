<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    protected $fillable = ['projeto_id', 'codigo', 'tipo', 'descricao'];
}
