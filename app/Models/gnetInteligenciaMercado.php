<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Session;

class gnetInteligenciaMercado extends Model
{
    protected $connection = 'mysql_gumanet';
    public $timestamps = false;
    protected $table = "tbl_comentarios";

    public function getComentarios()
    {
        return $this->hasMany(gnetInteligenciaComment::class, 'id_post', 'id');
    } 
}
