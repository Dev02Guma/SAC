<?php
namespace App\Http\Controllers;
use App\Models\Inventario;
use App\Models\Clientes;
use App\Models\Vendedor;
use App\Models\Liquidacion_a_6meses;
use App\Models\Liquidacion_a_12meses;
use App\Models\ClientesHistorico3M;
use App\Models\ClientesMora;
use App\Models\ClientesHistoricoFactura;
use App\Models\NivelPrecio;
use App\Models\Bodega;
use App\Models\ArticuloFavoritos;
use Illuminate\Http\Request;
use App\Models\Lotes;
use App\Models\Pedido;
use App\Models\PedidoComentario;
use App\Models\Usuario;
use App\Models\Factura;
use App\Models\Estadisticas;
use App\Models\MasterData;
use App\Models\PlanCrecimiento;
use App\Models\Promocion;
use Illuminate\Support\Facades\DB;
use Session;

class HomeController extends Controller {
    public function __construct()
    {
        $this->middleware('auth');
    }   
    
    public function getHome()
    {  
        $Normal ='';
        $SAC = '';
        $Lista_SAC = Usuario::getUsuariosSAC();  
        
        if (Session::get('rol') == '1' || Session::get('rol') == '2' || Session::get('rol') == '9') {
            $SAC = 'active';
        } else {
            $Normal = 'active';
        }
        
        $promocion = DB::table('promocion.tbl_promocion')->get();;
        return view('Principal.Home', compact('Lista_SAC','SAC','Normal', 'promocion'));
        
    }

    public function get8020()
    {  
        $Ruta = 'F10';
        $d1   = '2022-09-01';
        $d2   = '2022-09-30';

        $obj = MasterData::getData($Ruta,$d1,$d2);
        
        return response()->json($obj);
    }

    public function getEstadistiacas()
    {  
        $Normal ='';
        $SAC = '';
        $Lista_SAC = Usuario::getUsuariosSAC();  
        
        if (Session::get('rol') == '1' || Session::get('rol') == '2' || Session::get('rol') == '9') {
            $SAC = 'active';
        } else {
            $Normal = 'active';
        }
        
        return view('Principal.Estadisticas', compact('Lista_SAC','SAC','Normal'));
        
    }

    public function getStats($d1,$d2)
    { 
        $obj = Estadisticas::getData($d1,$d2);
        return response()->json($obj);
        //return view('Principal.Estadisticas', compact('Lista_SAC','SAC','Normal'));
        
    }

    public function getArticuloFavorito()
    {  
        return view('Principal.ArticuloFavorito');         
    }

    public function getMiProgreso($d1,$d2)
    {
        $dtaHome[] = array(
            'Estadistica'       => Estadisticas::getData($d1,$d2),
            'dtaVentasMes'      => Estadisticas::dtaVentasMes($d1,$d2),
            'dtaVentasRutas'    => Estadisticas::dtaVentasRutas($d1,$d2),
        );
        
        return response()->json($dtaHome);
    }
    public function dtaEstadisticas()
    {
        $d1 = date('Y-m-01', strtotime(now()));
        $d2 = date('Y-m-d', strtotime(now()));

        $dtaHome[] = array(
            'Estadistica'   => Estadisticas::getData($d1,$d2),           
        );
        return response()->json($dtaHome);
    }

    public function getData()
    {
        $dtaHome[] = array(
            'Inventario'    => Inventario::getArticulosFavoritos(),
            'Liq6Meses'     => Liquidacion_a_6meses::getArticulos(),
            'Liq12Meses'    => Liquidacion_a_12meses::getArticulos(),
            'Clientes'      => Clientes::getClientes(),
            'Vendedor'      => Vendedor::getVendedor(), 
        );
        
        return response()->json($dtaHome);
    }
    public function getArticulosFavoritos()
    {
        $dtaHome[] = array(
            'ArticulosFav'      => Inventario::getArticulosFavoritos(),
            'Inventario'        => Inventario::getArticulosSinFav(),
        );
        
        return response()->json($dtaHome);
    }
    public function AddFavs(Request $request)
    {
        $response = ArticuloFavoritos::AddFavs($request);
        return response()->json($response);
    }
    public function getDataCliente($idCliente)
    {

        $articulos_favoritos = array();

        $Lista_articulos_Favoritos = ArticuloFavoritos::all();
        
        foreach ($Lista_articulos_Favoritos as $rec){
            $articulos_favoritos[] = $rec->Articulo;
        }

        $dtaCliente[] = array(
            'InfoCliente'                   => Clientes::where('CLIENTE', $idCliente)->get(),
            'PlanCrecimieto'                => Clientes::getPlanCrecimiento($idCliente),
            'Historico3M'                   => ClientesHistorico3M::where('CLIENTE', $idCliente)->get(),
            'ClienteMora'                   => ClientesMora::where('CLIENTE', $idCliente)->get(),
            'ClientesHistoricoFactura'      => ClientesHistoricoFactura::where('COD_CLIENTE', $idCliente)->orderBy('Dia', 'DESC')->get(),            
            'ArticulosNoFacturado'          => Inventario::whereNotIn('ARTICULO', function($q)  use ($idCliente){
                                                    $q->select('ARTICULO')->from('PRODUCCION.dbo.GMV3_hstCompra_3M')->where("CLIENTE", $idCliente);
                                                })->whereIn('ARTICULO',$articulos_favoritos)->get(),            
        );

        return response()->json($dtaCliente);
    }
    public function getDataArticulo($idArticulo)
    {
        $dtaArticulo[] = array(
            'InfoArticulo'          => Inventario::where('ARTICULO', $idArticulo)->get(),
            'NivelPrecio'           => NivelPrecio::getNivelPrecio($idArticulo),
            'Bodega'                => Bodega::getBodega($idArticulo),
        );

        return response()->json($dtaArticulo);
    }

    public function getLotes($BODEGA,$ARTICULO)
    {
        $LOTES = Lotes::getLotes($BODEGA,$ARTICULO);
        return response()->json($LOTES);
    }
    public function ChancesStatus(Request $request)
    {
        $response = Pedido::ChancesStatus($request);
        return response()->json($response);
    }
    public function getDetallesFactura($Factura)
    {
        $response = Factura::getDetalles($Factura);
        return response()->json($response);
    }
    public function getCommentPedido(Request $request)
    {
        $response = PedidoComentario::getCommentPedido($request);
        return response()->json($response);
    }
    public function AddCommentPedido(Request $request)
    {
        $response = PedidoComentario::AddCommentPedido($request);
        return response()->json($response);
    }
    public function AddPlanCrecimiento(Request $request)
    {
        $response = PlanCrecimiento::AddPlanCrecimiento($request);
        return response()->json($response);
    }
    public function DeleteCommentPedido(Request $request)
    {
        $response = PedidoComentario::DeleteCommentPedido($request);
        return response()->json($response);
    }

    public function getPedidosRangeDates(Request $request)
    {
        $response = Pedido::getPedidos($request);
        return response()->json($response);
    }

    public function getCalendarPromocion()
    {       
        $articulos      = Inventario::getArticulos();
        return view('Principal.CalendarPromocion', compact('articulos'));         
    }

    public function getDataPromocion($mes, $anio){
        $promocion = DB::table('promocion.view_calendarpromocion')->where('nMonth', $mes)->where('nYear', $anio)->get();
        
        return response()->json($promocion);
    }

    public function insert_promocion(Request $request){
        $titulo = $request->Titulo;
        $descripcion = $request->Descripcion;
        $articulo = $request->Articulo;
        $articulotxt = $request->Articulo_Txt;
        $fechaIni = $request->fecha;
        $fechaFin = $request->dtaFechaFin;

        
        $promocion = new Promocion();

        $promocion->titulo = $titulo;
        $promocion->descripcion = $descripcion;
        $promocion->articulo = $articulo;
        $promocion->articulotxt = $articulotxt;
        $promocion->image = 'ico.PNG';
        $promocion->created_at = $fechaIni;
        $promocion->end_at = $fechaFin;

        $promocion->save();
        
        return response()->json($promocion);
    }
    
}
