<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Http\Request;

class UsuarioRutas extends Model {
    protected $table = "tbl_rutas_asginadas";
    public static function AsignarRuta(Request $request) {
        if ($request->ajax()) {
            try {

                $Id         = $request->input('Id');
                $valor      = $request->input('valor');

                $obj = new UsuarioRutas();   
                $obj->RUTA        = $valor;                
                $obj->id_usuario  = $Id;
                
                $response = $obj->save();

                return response()->json($response);
                
            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }
    }
    /**
     * Deja al usuario exactamente con las rutas recibidas (agrega las nuevas y quita las que sobran).
     */
    public static function Sincronizar($idUsuario, array $rutas)
    {
        $rutas  = array_values(array_unique(array_filter(array_map(function ($r) { return strtoupper(trim((string) $r)); }, $rutas))));
        $actual = UsuarioRutas::where('id_usuario', $idUsuario)->pluck('RUTA')->map(function ($r) { return strtoupper(trim($r)); })->all();

        $quitar  = array_diff($actual, $rutas);
        $agregar = array_diff($rutas, $actual);

        if (count($quitar)) {
            UsuarioRutas::where('id_usuario', $idUsuario)->whereIn('RUTA', $quitar)->delete();
        }
        foreach ($agregar as $ruta) {
            $obj = new UsuarioRutas();
            $obj->RUTA       = $ruta;
            $obj->id_usuario = $idUsuario;
            $obj->save();
        }
        return ['ok' => true, 'agregadas' => count($agregar), 'quitadas' => count($quitar)];
    }

    public static function Remover(Request $request)
    {
        if ($request->ajax()) {
            try {

                $id     = $request->input('id');
                
                $response = UsuarioRutas::where('id',$id)->delete();

                return response()->json($response);


            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }

    }
}
