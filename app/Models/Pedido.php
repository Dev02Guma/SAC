<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Auth;
use Session;
class Pedido extends Model {
    protected $connection = 'mysql_pedido';    
    protected $table = "tbl_order";

    public function Comentarios(){
        return $this->hasMany('App\Models\PedidoComentario','orden_code','code');
    }
    public function CountsComments(){
        return $this->Comentarios()->count();
    }
    public function getUsuario(){
        return $this->hasOne(UsuarioVendedor::class,'username','name');
    }
    public function show(Order $order){
        \DB::connection()->enableQueryLog();
        $data = $order->all();
        $queries = \DB::getQueryLog();
        return dd($queries);
    }
    public static function getPedidos(Request $request)
    {
        $Rutas          = array();
        $Condicionales  = array();
        $GrupoB         = array();

        $Estados        = ['PENDIENTE','PROCESADO','CANCELADO'];
        $ColorSt        = ['danger','success','info'];        

        $start      = $request->input('DateStart').' 00:00:00';
        $end        = $request->input('DateEnds').' 23:59:59';
        
        $Estado     = $request->input('Estado');
        $SAC        = $request->input('SAC');

        $rol = Session::get('rol');
        
        if($rol == 1 || $rol ==2 ){

            if($SAC == '0'){
                $Vendedor = Vendedor::getVendedor();

                foreach ($Vendedor as $v){
                    $Rutas[] = $v->VENDEDOR;
                }

                $GrupoB = ['F2614', 'F2606', 'F2707', 'F2708', 'F2804', 'F2802', 'F2822','F2911','F2503','F2513','F3009','F3010'];
                $Rutas = array_merge($Rutas, $GrupoB);
                
            }else{
                $Usuario = Usuario::where('username',$SAC)->get();
                foreach ($Usuario as $rec){            
                    foreach ($rec->Detalles as $Rts){
                        $Rutas[] = $Rts->RUTA;
                    }
                }
            }
            
        }else{            
            $Usuario = Usuario::where('id',Auth::id())->get();
            foreach ($Usuario as $rec){            
                foreach ($rec->Detalles as $Rts){
                    $Rutas[] = $Rts->RUTA;
                }
            }
        }

        if($Estado != -1){
            $Condicionales[] = ['status', '=', $Estado];
        }

        

        // withCount: el conteo de comentarios viene en la misma consulta (antes era 1 consulta por pedido).
        $query = Pedido::whereBetween('date_time', [$start, $end])->where($Condicionales)->whereIn('name',$Rutas)->withCount('Comentarios')->get();

        // Nombres de vendedor en una sola consulta (antes era 1 consulta por pedido).
        $codigosVendedor = $query->map(function ($fila) {
            return substr($fila["name"], 0, 3);
        })->unique()->values()->all();
        $NombresVendedor = $codigosVendedor
            ? Vendedor::whereIn('VENDEDOR', $codigosVendedor)->pluck('NOMBRE', 'VENDEDOR')
            : collect();

        $i = 0;
        $json = array();
        foreach ($query as $fila) {

            $CodeRuta = substr($fila["name"], 0, 3);

            $json[$i]["id"]             = $fila["id"];
            $json[$i]["code"]           = $fila["code"];
            $json[$i]["CLIENTE"]        = $fila["email"];
            $json[$i]["DESCRIPCION"]    = $fila["phone"];
            $json[$i]["DIRECCION"]      = $fila["address"];
            $json[$i]["RUTA"]           = $fila["name"];
            $json[$i]["VENDEDOR"]       = $NombresVendedor[$CodeRuta] ?? 'N/D';
            $json[$i]["FECHA"]          = date('M d, Y h:i A', strtotime($fila["created_at"])) ;
            $json[$i]["ARTICULOS"]      = $fila["order_list"];
            $json[$i]["MONTO"]          = $fila["order_total"];
            $json[$i]["COMMENT"]        = $fila["comment"];
            $json[$i]["ESTADO"]         = $Estados[$fila["status"]];
            $json[$i]["COLOR"]          = $ColorSt[$fila["status"]];
            $json[$i]["CountsComments"]          = $fila->comentarios_count;

            
            $i++;
        }
        return $json;
    }
    public static function ChancesStatus(Request $request)
    {
        if ($request->ajax()) {
            try {

                $id     = $request->input('id');
                $Valor  = $request->input('Valor');

                $response =   Pedido::where('id',  $id)->update([
                    "status" => $Valor,
                    "updated_by" => Auth::user()->id,
                ]);

                //Pedido::SendNotifications($request);

                return response()->json($response);


            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }

    }
    public static function SendNotifications(Request $request){
        if ($request->ajax()) {
            try {

                $id     = $request->input('id');
                $Valor  = $request->input('Valor');                

                $lblAccions  = ($Valor==1) ? 'Procesado' : 'Cancelado' ;
                
                $getPedido   = Pedido::where('id', $id)->first(['phone', 'player_id']);

                $NameCliente = $getPedido->phone;
                $Player_id   = $getPedido->player_id;
        
                $content = array("en" => "La orden de: $NameCliente ha sido $lblAccions.");
        
                $fields = array(
                    'app_id' => env('ONESIGNAL_APP_ID'),
                    'include_player_ids' => array($Player_id),
                    'data' => array("foo" => "bar", "cat_id"=> "1010101010"),
                    'headings'=> array("en" => $lblAccions),
                    'contents' => $content    
                );
        
                $fields = json_encode($fields);
        
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, env('ONESIGNAL_URL'));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                    'Authorization: Basic '.env('ONESIGNAL_REST_KEY')));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
                $response = curl_exec($ch);
                curl_close($ch);
                
            } catch (Exception $e) {
                $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
                return response()->json($mensaje);
            }
        }
    }



}
