<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagrama extends Model
{
    protected $fillable = ['projeto_id', 'tipo', 'caminho_imagem'];
}
