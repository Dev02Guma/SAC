<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class UsuarioVendedor extends Model {
    protected $connection = 'mysql_pedido';    
    protected $table = "tbl_admin";

}
