<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ArticuloFavoritos extends Model {
    protected $table = "tbl_articulos_favoritos";
    public static function AddFavs(Request $request)
    {
        if ($request->ajax()) {
            try {

                $Articulo  = $request->input('Articulo');

                $isExist = ArticuloFavoritos::where('Articulo',$Articulo)->get();

                


                if (count($isExist) >= 1) {
                    
                    $response = ArticuloFavoritos::where('Articulo',$Articulo)->delete();
                }else{

                    $Obj = new ArticuloFavoritos();
                    $Obj->Articulo    = $Articulo;      
                    $response = $Obj->save();

                }

                // Inventario lee los favoritos de caché (5 min): se limpia para que el cambio se vea al instante.
                cache()->forget('ArticuloFavoritos');

                return response()->json($response);

            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }

    }
}