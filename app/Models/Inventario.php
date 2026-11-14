<?php

namespace App\Models;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Inventario extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;
    protected $table = "PRODUCCION.dbo.iweb_articulos";

    public function cuarentena(): HasOne{
        return $this->hasOne(Cuarentena::class, 'ARTICULO', 'ARTICULO');
    }

    public static function getArticulos()
    {
        return Inventario::get();
    }
    
    public static function getArticulosSinFav()
    {
        //$articulos_favoritos = array();
        $lista_negra = array();

        $ArticuloFavoritosCache = cache()->remember('ArticuloFavoritos', now()->addMinutes(5), function () {
            return ArticuloFavoritos::all();
        })->pluck('Articulo')->toArray();

        $ArticuloListaNegraCache = cache()->remember('ArticuloListaNegraCache', now()->addMinutes(5), function () {
            return DB::connection('sqlsrv')->select('SELECT * FROM PRODUCCION.dbo.tbl_articulos_lista_negra');
        });
        foreach($ArticuloListaNegraCache as $lista){
            $lista_negra[] = $lista->articulo;
        }
        return Inventario::whereNotIn('ARTICULO',$ArticuloFavoritosCache)->whereNotIn('ARTICULO',$lista_negra)->get();
        
    }
    public static function getArticulosFavoritos()
    {
        $articulos_favoritos = array();
        $listaArticulos = $json =  array();
        $i = 0;

        $cachedArticulosArray = Redis::get('ArticuloFavoritos' );
        if ($cachedArticulosArray) {
            $ArticuloCache = $cachedArticulosArray;
        } else {
            $ArticuloCache = cache()->remember('ArticuloFavoritos', now()->addMinutes(5), function () {
                return ArticuloFavoritos::all();
            })->pluck('Articulo')->toArray();
        }
        
        $listaArticulos = Inventario::with('cuarentena')->whereIn('ARTICULO',$ArticuloCache)->get();
        $Info_Articulo = Productos::whereIn('product_sku', $ArticuloCache)->get();

        // Una sola ida a Redis para todas las URLs (antes era un GET por artículo).
        $imgKeys = $listaArticulos->map(function ($item) {
            return 'Arti_Url_Img_v2_' . $item->ARTICULO;
        })->all();
        $cachedImageUrls = $imgKeys ? array_combine($imgKeys, Redis::mget($imgKeys)) : [];
        $nuevasUrls = [];

        foreach($listaArticulos as $item){

            $img = "item.png";
            $json[$i]['ARTICULO'] = $item->ARTICULO;
            $json[$i]['CLASE_TERAPEUTICA'] = $item->CLASE_TERAPEUTICA;
            $json[$i]['DESCRIPCION'] = $item->DESCRIPCION;
            $json[$i]['total'] = $item->total;
            $json[$i]['UNIDADES'] = $item->UNIDADES;
            $json[$i]['LABORATORIO'] = $item->LABORATORIO;
            $json[$i]['UNIDAD_ALMACEN'] = $item->UNIDAD_ALMACEN;
            $json[$i]['006'] = $item['006'];
            $json[$i]['005'] = $item['005'];
            $json[$i]['PUNTOS'] = $item->PUNTOS;
            $json[$i]['PRECIO_FARMACIA'] = $item->PRECIO_FARMACIA;
            $json[$i]['IMPUESTO'] = $item->IMPUESTO;
            $json[$i]['REGLAS'] = $item->REGLAS;
            foreach($Info_Articulo as $row){
                if($item->ARTICULO == $row->product_sku){
                    $img = $row->product_image;
                    break;
                }
            }
            $json[$i]['IMG_NOMBRE'] = $img;
        
            $imgKey = 'Arti_Url_Img_v2_' . $item->ARTICULO;
            $imgUrl = $cachedImageUrls[$imgKey] ?? null;
            if (!$imgUrl) {
                $imgUrl = Storage::disk('s3')->temporaryUrl('product/'.$img, now()->addHours(12)); // válida 12 h; Redis la guarda 6 h
                $nuevasUrls[$imgKey] = $imgUrl;
            }

            $json[$i]['IMG_URL'] = $imgUrl;
            $json[$i]['cuarentena'] = $item->cuarentena;

            $i++;
        }

        // Guarda las URLs nuevas en un solo pipeline (6 h).
        if ($nuevasUrls) {
            Redis::pipeline(function ($pipe) use ($nuevasUrls) {
                foreach ($nuevasUrls as $key => $url) {
                    $pipe->setex($key, 21600, $url);
                }
            });
        }
        //dd($json);
        return $json;
    }

    public static function imgArticulo(Request $request){
        try{
            $product_name           = $request->input('nombre');
            $product_sku            = $request->input('sku');
            $product_price          = '100';
            $product_status         = 'Available';
            $product_description    = '<p>Pendiente</p>';
            $product_quantity       = '100';
            $category_id            = '20';
            $imagen                 = $request->file('nuevaImagen');
            $product_image          = $imagen->getClientOriginalName();
            
            
            
            $obj = new Productos();
            
            $obj->product_name          = $product_name;
            $obj->product_sku           = $product_sku;
            $obj->product_price         = $product_price;
            $obj->product_status        = $product_status;
            $obj->product_image         = $product_image;
            $obj->product_description   = $product_description;
            $obj->product_quantity      = $product_quantity;
            $obj->category_id           = $category_id;

            $response = $obj->save();

            if($response){
                Storage::put('product/'.$product_image, file_get_contents($imagen));
                alert()->success('Se ha guardado la imagen', 'Success');
                return redirect()->back();
            }else {
                alert()->error('hubo en problema al guardar la imagen', 'ERROR')->persistent('Close');
                return redirect()->back();
            }        
        }catch (Exception $e) {
            $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
            alert()->error($mensaje, 'ERROR')->persistent('Close');
            return redirect()->back();
        }
    }
}
