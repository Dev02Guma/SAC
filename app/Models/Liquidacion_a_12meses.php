<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Liquidacion_a_12meses extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;
    protected $table = "PRODUCCION.dbo.Vencimientos_12meses";

    public static function getArticulos()
    {
        $articulos_favoritos = array();
        $listaArticulos = $json =  array();
        $i = 0;
       

        $Lista_articulos_Favoritos = ArticuloFavoritos::all();
        
        foreach ($Lista_articulos_Favoritos as $rec){
            $articulos_favoritos[] = $rec->Articulo;
        }

        $listaArticulos = Liquidacion_a_12meses::all();

        $Info_Articulo = Productos::whereIn('product_sku', $articulos_favoritos)->get();
        foreach($listaArticulos as $item){
            $img = "item.png";
            $json[$i]['ARTICULO'] = $item->ARTICULO;
            $json[$i]['DESCRIPCION'] = $item->DESCRIPCION;
            $json[$i]['CANT_DISPONIBLE'] = $item->CANT_DISPONIBLE;
            $json[$i]['LOTE'] = $item->LOTE;
            $json[$i]['DIAS_VENCIMIENTO'] = $item->DIAS_VENCIMIENTO;
            $json[$i]['ESTADO_LOTE'] = $item->ESTADO_LOTE;
            $json[$i]['BODEGA'] = $item->BODEGA;
            $json[$i]['LABORATORIO'] = $item->LABORATORIO;
            $json[$i]['UNIDAD_VENTA'] = $item->UNIDAD_VENTA;
            $json[$i]['cantb'] = $item->cantb;
            $json[$i]['codigoclon'] = $item->codigoclon;
            $json[$i]['loteb'] = $item->loteb;
            $json[$i]['totalExistencia'] = $item->totalExistencia;
            $json[$i]['fecha_vencimientoR'] = $item->fecha_vencimientoR;
            $json[$i]['fecha_vencimientoB'] = $item->fecha_vencimientoB;
            foreach($Info_Articulo as $row){
                if($item->ARTICULO == $row->product_sku){
                    $img = $row->product_image;
                    break;
                }
            }
            $json[$i]['IMG_URL'] = Storage::Disk('s3')->temporaryUrl('product/'.$img, now()->addHours(12));
           $i++;
        }
        return $json;
    }
}
