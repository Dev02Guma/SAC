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
use App\Models\ClientesFull;
use App\Models\Comision;
use App\Models\Cuarentena;
use App\Models\PlanCrecimiento;
use App\Models\Promocion;
use App\Models\Logs;
use Illuminate\Support\Str;
Use UxWeb\SweetAlert\SweetAlert;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Psy\Readline\Hoa\Console;
use PDF;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style;
use PHPExcel_Style_Border;
use Session;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

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
        
        $promocion = Promocion::orderBy('fechaInicio')->where('Activo','S')->get();

        // Datos del artículo de cada promoción (laboratorio, unidad y regla de bonificación) para las tarjetas
        $artPromo = collect();
        try {
            $codigos = $promocion->pluck('articulo')->filter()->unique()->values()->all();
            if (count($codigos)) {
                $artPromo = Inventario::select('ARTICULO', 'LABORATORIO', 'UNIDAD_ALMACEN', 'REGLAS')
                    ->whereIn('ARTICULO', $codigos)->get()->keyBy('ARTICULO');
            }
        } catch (\Throwable $e) {
            $artPromo = collect();
        }

        // Artículos para el modal "Nueva promoción" (solo roles que administran promociones)
        $articulos = collect();
        if (Session::get('rol') == '1' || Session::get('rol') == '2') {
            $articulos = Inventario::select('ARTICULO', 'DESCRIPCION')->orderBy('DESCRIPCION')->get();
        }

        return view('Principal.Home', compact('Lista_SAC','SAC','Normal', 'promocion', 'articulos', 'artPromo'));
        
    }
    public function getEstadistiacas()
    {  
        $Normal ='';
        $SAC = '';
        $Lista_SAC = Usuario::getUsuariosSAC();  
        $Vendedor = Vendedor::ListVendedorPlanTrabajo();


        if (Session::get('rol') == '1' || Session::get('rol') == '2' || Session::get('rol') == '9') {
            $SAC = 'active';
        } else {
            $Normal = 'active';
        }

        
        return view('Principal.Estadisticas', compact('Lista_SAC','SAC','Normal','Vendedor'));
        
    }

    public function monitoring()
    {  
        $Normal ='';
        $SAC = '';
        $Lista_SAC = Usuario::getUsuariosSAC();  
        
        if (Session::get('rol') == '1' || Session::get('rol') == '2' || Session::get('rol') == '9') {
            $SAC = 'active';
        } else {
            $Normal = 'active';
        }
        
        return view('Principal.Monitoring', compact('Lista_SAC','SAC','Normal'));
        
    }
    public function getmonitoring($d1,$d2)
    {
        $dtaHome =Logs::dtaReporte($d1,$d2);
        
        return response()->json($dtaHome);
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
        $lista_negra = array();

        $Lista_articulos_Favoritos = ArticuloFavoritos::all();
        $getListaNegra = DB::connection('sqlsrv')->select('SELECT * FROM PRODUCCION.dbo.tbl_articulos_lista_negra');
        $getRifas = DB::connection('sqlsrv')->select('SELECT * FROM PRODUCCION.dbo.view_sac_rifa_num_cliente WHERE CLIENTE = '.$idCliente);
        $getNumRifas = !empty($getRifas) ? $getRifas[0]->TOTAL_ACCIONES : 0;

        foreach ($Lista_articulos_Favoritos as $rec){
            $articulos_favoritos[] = $rec->Articulo;
        }

        foreach($getListaNegra as $lista){
            $lista_negra[] = $lista->articulo;
        }

        $dtaCliente[] = array(
            'InfoCliente'                   => Clientes::where('CLIENTE', $idCliente)->get(),
            'PlanCrecimieto'                => Clientes::getPlanCrecimiento($idCliente),
            'Historico3M'                   => ClientesHistorico3M::where('CLIENTE', $idCliente)->get(),
            'ClienteMora'                   => ClientesMora::where('CLIENTE', $idCliente)->get(),
            'ClientesHistoricoFactura'      => ClientesHistoricoFactura::where('COD_CLIENTE', $idCliente)->orderBy('Dia', 'DESC')->get(),            
            'ArticulosNoFacturado'          => Inventario::whereNotIn('ARTICULO', function($q)  use ($idCliente){
                                                    $q->select('ARTICULO')->from('PRODUCCION.dbo.GMV3_hstCompra_3M')->where("CLIENTE", $idCliente);
                                                })->whereIn('ARTICULO',$articulos_favoritos)->whereNotIn('ARTICULO', $lista_negra)->get(),
            'Acciones'                      => $getNumRifas,
        );

        return response()->json($dtaCliente);
    }
    /**
     * Productos facturados a un cliente en un rango de fechas (líneas de cada factura).
     * Parámetros: ?desde=AAAA-MM-DD&hasta=AAAA-MM-DD (por defecto, los últimos 90 días).
     */
    public function getProductosFacturados(Request $request, $idCliente)
    {
        $fecha = function ($v, $defecto) {
            $d = \DateTime::createFromFormat('!Y-m-d', (string) $v);
            return ($d && $d->format('Y-m-d') === $v) ? $d : $defecto;
        };
        $hasta = $fecha($request->query('hasta'), new \DateTime('today'));
        $desde = $fecha($request->query('desde'), (clone $hasta)->modify('-90 days'));
        if ($desde > $hasta) { [$desde, $hasta] = [$hasta, $desde]; }
        $finExclusivo = (clone $hasta)->modify('+1 day');

        $filas = DB::connection('sqlsrv')->select(
            "SELECT TOP 5000 H.FACTURA, CONVERT(varchar(10), H.Dia, 23) AS FECHA,
                    D.ARTICULO, D.DESCRIPCION, D.CANTIDAD, D.PRECIO_UNITARIO, D.PRECIO_TOTAL
               FROM PRODUCCION.dbo.iweb4_historico_factura H
               INNER JOIN PRODUCCION.dbo.UMK_DETALLES_FACTURAS D ON D.FACTURA = H.FACTURA
              WHERE H.COD_CLIENTE = ? AND H.Dia >= ? AND H.Dia < ?
              ORDER BY H.Dia DESC, H.FACTURA DESC, D.LINEA",
            [(string) $idCliente, $desde->format('Y-m-d'), $finExclusivo->format('Y-m-d')]
        );

        return response()->json([
            'desde' => $desde->format('Y-m-d'),
            'hasta' => $hasta->format('Y-m-d'),
            'filas' => $filas,
        ]);
    }
    public function getDataArticulo($idArticulo)
    {
        $dtaArticulo[] = array(
            'InfoArticulo'          => Inventario::with('cuarentena')->where('ARTICULO', $idArticulo)->get(),
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

    public function getDataPromocion(){
        $promocion = Promocion::getDataCalendar();
        return response()->json($promocion);
    }

    public function insert_promocion(Request $request){
        try{

            $titulo = $request->title;
            $descripcion = $request->description;
            $articulo = explode("!",$request->label);
            $fechaIni = $request->startDate;
            $fechaFin = $request->endDate;
            $name = "item.jpg";
            $activo = $fechaFin >= date('Y-m-d') ? 'S' : 'N';

            if($fechaFin >= $fechaIni){
                $promocion = new Promocion();

                if($request->hasFile('nuevaImagen')){
                    $file = $request->file('nuevaImagen');

                    $name = time() . '-' . $file->getClientOriginalName();
                    Storage::disk('s3')->put('Promociones/'.$name, file_get_contents($file));
                
                }
                
                $promocion->titulo = $titulo;
                $promocion->descripcion = $descripcion;
                $promocion->articulo = $articulo[0];
                $promocion->nombre = $articulo[1];
                $promocion->image = $name;
                $promocion->fechaInicio = $fechaIni;
                $promocion->fechaFinal = $fechaFin;
                $promocion->activo = $activo;
                $promocion->save();

                alert()->success('Se ha registrado la promocion', 'Success');
                return redirect()->back();
            }else{
                alert()->error('La fecha final no puede ser inferior a la fecha de inicio', 'ERROR')->persistent('Close');
                return redirect()->back();
            }
        }catch (Exception $e) {
            $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
            alert()->error($mensaje, 'ERROR')->persistent('Close');
            return redirect()->back();
    }   
    }

    public function update_promocion(Request $request){
        try{
            $id = $request->eIdPromocion;
            $titulo = $request->eTitle;
            $descripcion = $request->eDescription;
            $articulo = explode("!",$request->eLabel);
            $fechaIni = $request->eStartDate;
            $fechaFin = $request->eEndDate;
            $name = $request->fotoActual;
            $at = $fechaFin >= date('Y-m-d') ? 'S' : 'N';

            if($fechaFin >= $fechaIni){
                $promocion = new Promocion();

                if($request->hasFile('eNuevaImagen')){
                    $file = $request->file('eNuevaImagen');
                    
                    $name = time() . '-' . $file->getClientOriginalName();
                    
                    Storage::disk('s3')->put('Promociones/'.$name, file_get_contents($file));
                    
                }

                $promocion = DB::table('tbl_promocion')->where('id', $id)->update(['titulo'=>$titulo,'descripcion'=>$descripcion,'articulo'=>$articulo[0],'nombre'=>$articulo[1],'image'=>$name,'fechaInicio'=>$fechaIni,'fechaFinal'=>$fechaFin,'activo'=>$at]);
                alert()->success('Se ha modificado la promocion', 'Success');
                return redirect()->back();
            }else{
                alert()->error('La fecha final no puede ser inferior a la fecha de inicio', 'ERROR')->persistent('Close');
                return redirect()->back();
            }
        }catch (Exception $e) {
            $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
            alert()->error($mensaje, 'ERROR')->persistent('Close');
            return redirect()->back();
        }

    }

    public function editPromocion($id, $activo){
        $at = $activo == 'S' ? 'N' : 'S';
        $promocion = DB::table('tbl_promocion')->where('id', $id)->update(['activo'=>$at]);
        return response()->json($promocion);
    }
    
    public function getPlanCrecimientoIco(){
        $ico = PlanCrecimiento::get();
        return $ico;

    }

    public function generarPDF(){
        $inventario = Inventario::getArticulos()->toArray();
        $pdf = PDF::loadView('Principal.invPDF', compact('inventario'));
        return $pdf->download('inventario.pdf');
    }

    public function generarExcel() {

		$obj = Inventario::getArticulos();

		$objPHPExcel = new PHPExcel();
        $tituloReporte = "Inventario Totalizado";
		
        $titulosColumnas = array('Codigo', 'Descripción', 'Cant. Disponible 002','Cant. Disponible 005', 'Precio Farmacia', 'Producto Bonificado');
		$objPHPExcel->setActiveSheetIndex(0)
                        ->mergeCells('A1:F1');
		$objPHPExcel->setActiveSheetIndex(0)
                        ->mergeCells('A2:F2');

        $estiloTituloReporte = array(
            'font' => array(
            'name'      => 'Calibri',
            'bold'      => true,
            'italic'    => false,
            'strike'    => false,
            'size'      => 14,
            'color'     => array(
                            'rgb' => '212121')
            ),
            'alignment' =>  array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'rotation'   => 0,
                            'wrap'       => TRUE,
                            )
        );

        $estiloTituloColumnas = array(
            'font' => array(
                        'name'  => 'Calibri',
                        'bold'  => true
            ),
            'alignment' =>  array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                'wrap'          => TRUE
                            ),
            'borders' => array(
                            'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                        ),
            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                            )
            )
        );

        $estiloInformacion = new PHPExcel_Style();
        $estiloInformacion->applyFromArray(
            array(
                'borders' => array(
                'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                        ),
                'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                ),
                )
            )
        );

        $right = array(
            'alignment' =>  array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => TRUE
            )
        );

        $left = array(
            'alignment' =>  array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => TRUE
            )
        );

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1',    $tituloReporte)
		->setCellValue('A3',    $titulosColumnas[0])
		->setCellValue('B3',    $titulosColumnas[1])
		->setCellValue('C3',    $titulosColumnas[2])
		->setCellValue('D3',    $titulosColumnas[3])
        ->setCellValue('E3',    $titulosColumnas[4])
        ->setCellValue('F3',    $titulosColumnas[5]);

		$i=4;
		foreach ($obj as $key) {
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i,  $key['ARTICULO'])
			->setCellValue('B'.$i,  $key['DESCRIPCION'])
			->setCellValue('C'.$i,  $key['total'])
            ->setCellValue('D'.$i,  $key['005'])
			->setCellValue('E'.$i,  $key['PRECIO_FARMACIA'])
			->setCellValue('F'.$i,  $key['REGLAS']);
			
			$i++;
		}

		$objPHPExcel->getActiveSheet()->setTitle('INVENTARIO');
		$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estiloTituloReporte);
		$objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray($estiloTituloColumnas);      
		$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:F".($i-1));

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(100);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(60);

		$objPHPExcel->getActiveSheet()->getStyle('C3:F'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Inventario totalizado hasta '.date('d/m/Y').'.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');


	}

    public function imgArticulo(Request $request){
        $result = Inventario::imgArticulo($request);
        return $result;
    }

    public function insertCuarentena(Request $request){
        try{
            $articulo = $request->input('Articulo');
            $op = $request->input('Opcion');
            $id_user = Auth::id();
            if($op == 1){
                $created_at = today();

                $cuarentena = new Cuarentena();

                $cuarentena->ARTICULO = $articulo;
                $cuarentena->created_at = $created_at;
                $cuarentena->id_user = $id_user;
                
                $response = $cuarentena->save();
            }else{
                $response = Cuarentena::where('ARTICULO', $articulo)->delete();
            }

            return $id_user;
        }catch (Exception $e) {
            $mensaje =  'Excepción capturada: ' . $e->getMessage() . "\n";
            alert()->error($mensaje, 'ERROR')->persistent('Close');
            return redirect()->back();
        }
    }

}
