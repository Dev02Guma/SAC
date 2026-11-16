<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Session;

class gnetInteligenciaComment extends Model
{
    protected $connection = 'mysql_gumanet';
    public $timestamps = false;
    protected $table = "tbl_comments_post_im";
}
