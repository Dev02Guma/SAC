<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Controlados extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;
    protected $table = "Softland.dbo.ANA_VentasTotales_MOD_Contabilidad_UMK";

    public static function getData($request) {
        
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $dta = [];

        $Rows = Controlados::whereBetween('Fecha_de_factura', [$desde, $hasta])->where('ANULADA', 'N')->Where('BODEGA', '005')->get();

        foreach ($Rows as $key => $row) {
            $dta[$key] = [
                'FECHA_DE_FACTURA' => date('Y-m-d', strtotime($row->Fecha_de_factura)),
                'FACTURA' => $row->FACTURA,
                'ARTICULO' => $row->ARTICULO,
                'DESCRIPCION' => strtoupper($row->DESCRIPCION),
                'CANTIDAD_FACT' => number_format($row->CANTIDAD_FACT, 2),
                'LOTE' => $row->LOTE,
                'BODEGA' => $row->BODEGA,
                'ANULADA' => $row->ANULADA,
                'CLIENTE_CODIGO' => $row->CLIENTE_CODIGO,
                'Name_cliente' => strtoupper($row->Nombre_Cliente),
                'PRECIO_UNITARIO' => number_format($row->PRECIO_UNITARIO, 2),
                'VENTA_TOTAL' =>  number_format($row->venta_total, 2),
            ];
        }

        return $dta;
    }
}