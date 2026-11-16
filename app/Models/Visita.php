<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Http\Request;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\DB;
use SoapClient;
use SoapFault;

class Visita extends Model {
    protected $table = "db_sac_app.tbl_visitas";

 
    
    public static function Add(Request $request)
    {

        if ($request->ajax()) {
            try {

                $Cliente    = $request->input('Cliente');
                $FechaVi    = $request->input('FechaVi');
                $Descrip    = $request->input('Descrip');
                $Ruta       = $request->input('Ruta');

                $iCliente   = ClientesFull::where('CLIENTE',$Cliente)->first();          

                $checkVisit = Visita::where('cliente', $Cliente)->where('fechaVisita', 'LIKE', '%'.date('Y-m-d',strtotime($FechaVi)).'%')->first();

                if($checkVisit == null){
                   
                    $obj = new Visita();   
                    $obj->cliente       = $Cliente;   
                    $obj->ruta          = $Ruta;
                    $obj->nombre        = $iCliente->NOMBRE;                
                    $obj->fechavisita   = $FechaVi;
                    $obj->descripcion   = $Descrip;
                    $obj->activo        = 'S';                 
                    $response = $obj->save();

                    return $response;
                }else{
                    return false;
                }
                
                
            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }
        
    }
    public static function reutilizar(Request $request)
    {

        if ($request->ajax()) {
            try {
                
                $ruta       = $request->input('ruta');
                $dtIni      = $request->input('startDate');
                $dtEnd      = $request->input('endDate');
                
                $NewFechas  = [];

                $Clientes_Visitas = Visita::where('activo', 'S')
                ->where('ruta', $ruta)
                ->whereBetween('fechavisita', [$dtIni, $dtEnd])
                ->get();


                foreach ($Clientes_Visitas as $key => $f) {
                    $dt = new DateTime($f->fechavisita);
                    $dt->add(new DateInterval('P1W')); 
                    $NewFechas[] = [
                        'ruta'      => $f->ruta,
                        'cliente'   => $f->cliente, 
                        'nombre'    => $f->nombre,
                        'activo'    => 'S',
                        'fechavisita'   => $dt->format('Y-m-d H:i:s')
                    ];
                }
                $response = Visita::insert($NewFechas); 
                return $response;
                
            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }
        
    }
    public static function getData($Ruta){
        $array = array();

        $result = Visita::where('activo','S')->where('ruta',$Ruta)->get();
        foreach($result as $key => $row ){
            $array[$key] = [
                'id'            => $row->id,
                'fechaInicio'   => $row->fechavisita,
                'descripcion'   => $row->descripcion,
                'nombre'        => $row->nombre,
                'cliente'       => $row->cliente,
                'efectiva'      => $row->efectiva,
                'time_ini'      => $row->time_ini,
                'time_end'      => $row->time_end,
                'orden'         => $row->orden,
            ];
           
        }

        return $array;
    }
    public static function UpdateVisita(Request $request)
    {
        if ($request->ajax()) {
            try {

                $id     = $request->input('id');
                $date   = $request->input('date');
                
                $response =   Visita::where('id',  $id)->update([
                    "fechavisita" => $date,
                ]);

                return response()->json($response);


            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }

    }
    public static function CheckVisita(Request $request)
    {
        if ($request->ajax()) {
            try {

                $id             = $request->input('id');
                $startDate      = $request->input('startDate');
                $endDate        = $request->input('endDate');
                $isEfective     = $request->input('isEfective');

                $startDateFormatted = date('H:i', strtotime($startDate));
                $endDateFormatted = date('H:i', strtotime($endDate));
                
                $response =   Visita::where('id',  $id)->update([
                    "time_ini" => $startDateFormatted,
                    "time_end" => $endDateFormatted,
                    "efectiva" => $isEfective,
                ]);

                return response()->json($response);


            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }

    }
    public static function rmVisita($id)
    {
        try {
                
            $delete = Visita::where('id', $id )->delete();
            return  $delete;               
        } catch (Exception $e) {
            $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
            return response()->json($mensaje);
        }
    }

    public function ordenes(){
        return $this->hasMany('App\Models\GmvPedidos', 'name', 'ruta');
    }


    public static function validarVisita(){

        $json = array();
        $pedidosAgrupados = [];
        $i = 0;

        $fechaIni = Carbon::now()->startOfDay();
        $fechaFin = Carbon::now()->endOfDay();
        //$fechaIni = '2024-01-12 00:00:00';
        //$fechaFin = '2024-01-12 23:59:59';

        $visitas = Visita::whereBetween('fechavisita', [$fechaIni, $fechaFin])->pluck('cliente')->toArray();
       
        
        $ordenes = MasterPedidos::whereIn('Cliente', $visitas)->whereBetween('created_at', [$fechaIni, $fechaFin])->get();
     
        foreach($ordenes as $or){
            $pedido = '['.$or->code.' / C$ '.number_format($or->Valor,2,'.',',').']';
            $cliente = $or->Cliente;

            if (!isset($pedidosAgrupados[$cliente])) {
                $pedidosAgrupados[$cliente] = [];
                $json[] = $cliente;
            }

            $pedidosAgrupados[$cliente][] = $pedido;
            
        }

        //dd($pedidosAgrupados);
       
        foreach ($pedidosAgrupados as $cliente => $pedidos) {
            $pedidosConcatenados = implode(' ; ', $pedidos);
        
            Visita::where('cliente', $cliente)
                ->whereBetween('fechavisita', [$fechaIni, $fechaFin])
                ->update(['efectiva' => 1, 'orden' => $pedidosConcatenados]);
        }

        Visita::whereNotIn('cliente', $json)
                ->where('efectiva', 0)
                ->whereBetween('fechavisita', [$fechaIni, $fechaFin])
                ->update(['efectiva' => 2]);

        
        return $pedidosAgrupados;
    }
}
