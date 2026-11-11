<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'tbl_promocion';

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(){

        if(!is_null($this->image)){
            return Storage::temporaryUrl('Promociones/'.$this->image, now()->addHours(12));
        }
        return null;
    }

    public static function getDataCalendar(){
        $array = array();
        $i = 0;

        $result = promocion::get();

        foreach($result as $row){
            $array[$i]['id'] = $row->id;
            $array[$i]['titulo'] = $row->titulo;
            $array[$i]['fechaInicio'] = $row->fechaInicio;
            $array[$i]['fechaFinal'] = $row->fechaFinal;
            $array[$i]['descripcion'] = $row->descripcion;
            $array[$i]['nombre'] = $row->nombre;
            $array[$i]['articulo'] = $row->articulo;
            $array[$i]['image'] = $row->image;
            $array[$i]['activo'] = $row->activo;
            if(!is_null($row->image)){
                $array[$i]['image_url'] = Storage::temporaryUrl('Promociones/'.$row->image, now()->addHours(12));
            }
            $i++;
        }

        return $array;
    }
}
