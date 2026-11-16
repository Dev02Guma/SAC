<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Session;


class Vendedor extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;
    protected $table = "PRODUCCION.dbo.vtVS2_Vendedores";


    public static function getVendedor()
    {  
        return Vendedor::whereNotIn('VENDEDOR',['F01','F12','F02',"F15","F23"])->get();
        //return Vendedor::whereIn('VENDEDOR',['F05'])->get();
    }

    public static function getZonas($Ruta){
        $rZonas = DB::connection('mysql_gumanet')->select("SELECT Zona FROM zonas WHERE Ruta= '".$Ruta."' ");

        return $rZonas;
    }
    public static function ListVendedorPlanTrabajo(){  
        if (Session::get('rol') == '9' || Session::get('rol') == '13') {
            $id_user = Auth::id();            
            $data_ruta = Estadisticas::get_rutas_group($id_user);
            $Rutas = isset($data_ruta['ruta']) ? explode(',', $data_ruta['ruta']) : [];
            $Rutas = array_filter(array_unique($Rutas));
            $Rutas = array_map(function($ruta) {
                return trim($ruta, "'");
            }, $Rutas);
        } else {
            $Rutas = [];
        }

    $vendedores = Vendedor::whereIn('VENDEDOR', $Rutas)->get();

    return $vendedores;
    }

}
