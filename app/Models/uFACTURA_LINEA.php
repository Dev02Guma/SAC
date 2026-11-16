<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class uFACTURA_LINEA extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;
    protected $table = "PRODUCCION.dbo.view_sac_devoluciones";


    public static function getData(Request $request)
    {  
        $array[0] = [
                'FACTURAS' => ' 0000 ',
                'FCT_DATE' => ' Mes 01, 2000  ',
                'FCT_RUTA' => ' F00 ',
                'FCT_NAME' => ' NOMBRE CLIENTE ',
                'FCT_ARTI' => ' - ',
                'FCT_DESC' => ' - ',
                'FCT_CANT' => ' - ',
                'FCT_LOTE' => ' - ',
                'FCT_CLIE' => ' 0000 ',
                'FCT_BONI' => ' N/D ',
            ];

        $FCT    = $request->input('FACTURA');

        $result = uFACTURA_LINEA::WHERE('FACTURA',$FCT)->get();


        foreach ($result as $key => $row) {

            $array[$key] = [
                'FACTURAS' => $row->FACTURA,
                'FCT_DATE' => $row->FECHA_FACTURA,
                'FCT_RUTA' => $row->VENDEDOR,
                'FCT_NAME' => $row->NOMBRE_CLIENTE,
                'FCT_ARTI' => ($row->PRECIO_UNITARIO <= 0) ?  strtoupper($row->ARTICULO) . ' - ( Bonif ) ' : strtoupper($row->ARTICULO),
                'FCT_DESC' => strtoupper($row->DESCRIPCION),    
                'FCT_CANT' => number_format($row->CANTIDAD,2),
                'FCT_LOTE' => $row->LOTE,
                'FCT_CLIE' => $row->CLIENTE,
                'FCT_BONI' => ($row->PRECIO_UNITARIO <= 0) ? 'style="background-color: rgba(125, 221, 176, 0.18);"' : " " 
            ];
        }

        return $array;
    }

}
