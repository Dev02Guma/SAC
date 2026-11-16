<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class Estadisticas extends Model
{
    public function __construct() {
        $this->middleware('auth');
    }
    public static function get_rutas_group($Id)
    {
        
        $UsrName = DB::table('users')->where('id', $Id)->pluck('username');

        $datos_rutas = DB::table('view_rutas')->where('id', $UsrName)->get();

        $json = array();
        $i = 0;

        if (count($datos_rutas) > 0) {
            foreach ($datos_rutas as $key => $value) {

                $json['id']     = $value->id;
                $json['ruta']   = $value->RUTA;
                

                $i++;
            }
        }

        return $json;
    }
    static function getLabelActual(){

        $day = date('w');
        $week_start = date('d', strtotime('monday this week'));
        $week_end = date('d', strtotime('sunday this week'));
        $week_month = date('F', strtotime('sunday this week'));

        return "Del ". $week_start ." al ".$week_end." de ".$week_month.": ";
    }

    public static function getData($d1,$d2){

        $data = array();
        $i=0;
        $sql_exec = '';
        

        if (Session::get('rol') == '9' || Session::get('rol') == '13') {
            
            $id_user = Auth::id();            
            $data_ruta = Estadisticas::get_rutas_group($id_user);
            $Rutas = " AND VENDEDOR IN ( ".$data_ruta['ruta']." ) ";
        } else {
            $Rutas = '';
        }

        
        $w = date("W", strtotime($d2));
        $y = date("Y", strtotime($d2));

        $SAC_into_vendedor = array(
            ["RUTA" => "F03","SAC" => "AURA","ZONA" => "MGA ABAJO NORTE"],
            ["RUTA" => "F05","SAC" => "AURA","ZONA" => "MGA ARRIBA"],
            ["RUTA" => "F21","SAC" => "AURA","ZONA" => "N/D"],
            ["RUTA" => "F19","SAC" => "NADIESKA","ZONA" => "OCCIDENTE"],
            ["RUTA" => "F06","SAC" => "NADIESKA","ZONA" => "LEON"],
            ["RUTA" => "F14","SAC" => "NADIESKA","ZONA" => "CHINANDEGA"],
            ["RUTA" => "F13","SAC" => "NADIESKA","ZONA" => "MGA ABAJO SUR"],
            ["RUTA" => "F07","SAC" => "YESSICA","ZONA" => "MYA-GDA"],
            ["RUTA" => "F23","SAC" => "YESSICA","ZONA" => "SUR ORIENTE"],
            ["RUTA" => "F09","SAC" => "REYNA","ZONA" => "EST-NS-MAD"],
            ["RUTA" => "F10","SAC" => "REYNA","ZONA" => "MAT-JIN"],
            ["RUTA" => "F22","SAC" => "REYNA","ZONA" => "N/D"],
            ["RUTA" => "F08","SAC" => "REYNA","ZONA" => "CAR-RIV"],
            ["RUTA" => "F11","SAC" => "YORLENI","ZONA" => "CHON-RSJ-RAAS"],
            ["RUTA" => "F20","SAC" => "YORLENI","ZONA" => "BOACO- RAAN"],
            ["RUTA" => "F02","SAC" => "ALEJANDRA","ZONA" => "INSTIT"],
            ["RUTA" => "F04","SAC" => "ALEJANDRA","ZONA" => "MCDO/MAYORISTAS"],
            ["RUTA" => "F15","SAC" => "","ZONA" => "VENTAS GERENCIA"],
            ["RUTA" => "F18","SAC" => "","ZONA" => ""],
        );

        $sql_exec = "SELECT
        T0.VENDEDOR,
        ISNULL((SELECT META_RUTA FROM PRODUCCION.dbo.rpt_informe_ventas_metas_rutas T2 WHERE T2.RUTA = T0.VENDEDOR),0) META_RUTA,
        ISNULL((SELECT COUNT(DISTINCT T1.CLIENTE) FROM Softland.UMK.PEDIDO AS T1 WHERE T1.ESTADO= 'F'AND T1.FECHA_PEDIDO BETWEEN '".$d1."'  AND '".$d2."' AND T1.VENDEDOR = T0.VENDEDOR ), 0) AS CLIENTE,
        ISNULL((SELECT T3.META_CLIENTE FROM PRODUCCION.dbo.tbl_meta_cliente_rutas T3 WHERE T3.RUTA = T0.VENDEDOR AND T3.MES = MONTH( '".$d1."') AND T3.ANNIO= YEAR( '".$d1."')),0) AS META_CLIENTE,
        SUM(T0.TOTAL_LINEA) as MesActual,
        ISNULL((SELECT  sum(T4.VentaNetaLocal) Venta FROM Softland.dbo.ANA_VentasTotales_MOD_Contabilidad_UMK T4  WHERE T4.Fecha_de_factura = '".$d2."' AND T4.VENDEDOR = T0.VENDEDOR    ), 0) AS DiaActual,	
        ISNULL((SELECT  sum(T4.VentaNetaLocal) Venta FROM Softland.dbo.ANA_VentasTotales_MOD_Contabilidad_UMK T4  WHERE (DATEPART(week,T4.Fecha_de_factura)-1) = '".$w."' AND DATEPART(YY,T4.Fecha_de_factura) = '".$y."'  AND T4.VENDEDOR = T0.VENDEDOR    ), 0) AS SaleWeek,	
        ISNULL((SELECT COUNT(DISTINCT T1.ARTICULO) FROM PRODUCCION.dbo.view_master_pedidos_with_cadena AS T1 WHERE T1.FECHA_PEDIDO BETWEEN '".$d1."' AND '".$d2."'AND T1.VENDEDOR = T0.VENDEDOR ), 0) AS SKU,	
        ISNULL((SELECT SUM(T1.TOTAL_LINEA) AS NoV FROM PRODUCCION.dbo.view_master_pedidos_with_cadena AS T1 WHERE T1.FECHA_PEDIDO BETWEEN '".$d1."'  AND '".$d2."' AND T1.VENDEDOR = T0.VENDEDOR and   T1.PEDIDO NOT LIKE 'PT%'  ), 0) AS EJEC,
        ISNULL((SELECT SUM(T1.TOTAL_LINEA) AS NoV FROM PRODUCCION.dbo.view_master_pedidos_with_cadena AS T1 WHERE T1.FECHA_PEDIDO BETWEEN '".$d1."'  AND '".$d2."' AND T1.VENDEDOR = T0.VENDEDOR and   T1.PEDIDO LIKE 'PT%'    ), 0) AS SAC
    
        FROM
            PRODUCCION.dbo.view_master_pedidos_with_cadena T0 	
            
        WHERE
            T0.FECHA_PEDIDO BETWEEN '".$d1."' AND '".$d2."'  AND T0.VENDEDOR NOT IN ( 'F01', 'F12' ) ".$Rutas."
        GROUP BY T0.VENDEDOR";

        


        $sql_skus = "SELECT 	( 
        SELECT COUNT(DISTINCT T0.ARTICULO) FROM	PRODUCCION.dbo.view_master_pedidos_with_cadena T0 
        WHERE T0.FECHA_PEDIDO BETWEEN DATEADD( m, DATEDIFF( m, 0, '".$d1."' ), 0 ) AND dateadd( DD, - 1, CAST ( '".$d2."'AS DATE ) ) 
        AND T0.VENDEDOR NOT IN ( 'F01','F02', 'F04','F15','F12' ) 
        ) SKU_Farmacia,
        ( 
            SELECT COUNT(DISTINCT T0.ARTICULO) FROM	PRODUCCION.dbo.view_master_pedidos_with_cadena T0
            WHERE T0.FECHA_PEDIDO BETWEEN DATEADD( m, DATEDIFF( m, 0, '".$d1."' ), 0 ) AND dateadd( DD, - 1, CAST ( '".$d2."'AS DATE ) ) 
            AND T0.VENDEDOR IN ( 'F02', 'F04' ) 
        ) SKU_Proyect02,
        COUNT(DISTINCT T0.ARTICULO) SKU_TODOS
        FROM
        PRODUCCION.dbo.view_master_pedidos_with_cadena T0
        WHERE
        T0.FECHA_PEDIDO BETWEEN '".$d1."' AND '".$d2."' AND T0.VENDEDOR NOT IN ( 'F01', 'F12' ) ";
            
            

        $rSKU_Facturados = DB::connection('sqlsrv')->Select($sql_skus);
        $Fecha_Periodo  = date('Y-m',strtotime($d1))."-01";        

        $sql_dias_habiles="SELECT T0.dias_facturados,T0.dias_habiles FROM DESARROLLO.dbo.metacuota_GumaNet T0 WHERE T0.Fecha = '".$Fecha_Periodo."'";
        $rDiasHabiles = DB::connection('sqlsrv')->Select($sql_dias_habiles);        

        $data['SKU_Farmacia'] = floatval($rSKU_Facturados[0]->SKU_Farmacia);
        $data['SKU_Proyect02'] = floatval($rSKU_Facturados[0]->SKU_Proyect02);
        $data['SKU_TODOS'] = floatval($rSKU_Facturados[0]->SKU_TODOS);

        $var_dias_habiles = (!isset($rDiasHabiles[0]->dias_habiles)) ? 1 : floatval($rDiasHabiles[0]->dias_habiles) ; 
        $var_dias_factura =  (!isset($rDiasHabiles[0]->dias_facturados)) ? 1 : floatval($rDiasHabiles[0]->dias_facturados) ; 
        
        $porcen_dias = ($var_dias_habiles / $var_dias_factura) * 100 ;
        
        $data['Dias_Habiles'] = $var_dias_habiles;
        $data['Dias_Facturados'] = $var_dias_factura; 
        $data['Dias_porcent'] = number_format($porcen_dias,0) . " %"; 

        $query = DB::connection('sqlsrv')->Select($sql_exec);


        $sql_vendedor = "SELECT VENDEDOR, NOMBRE FROM Softland.umk.VENDEDOR WHERE ACTIVO='S' AND VENDEDOR != 'LPCM'";
        $rVendedor = DB::connection('sqlsrv')->Select($sql_vendedor);

        $Venta_Meta = 0 ; 
        $Venta_Real = 0 ; 
        $Venta_Porc = 0 ;
        $Venta_Actu = 0;
        $Venta_Week = 0;

        $Cliente_Meta = 0 ; 
        $Cliente_Real = 0 ; 
        

        $data['Venta_Meta'] = $Venta_Meta;
        $data['Venta_Real'] = $Venta_Real;
        $data['Venta_Porc'] = $Venta_Porc;
        $data['Venta_Actu'] = $Venta_Actu;
        $data['Venta_Week'] = $Venta_Week;

        $data['Cliente_Meta'] = $Cliente_Meta;
        $data['Cliente_Real'] = $Cliente_Real;

        
   



        if( count($query)>0 ) {
            foreach ($query as $key) {

                $index_key = array_search($key->VENDEDOR, array_column($rVendedor, 'VENDEDOR'));


                $data[$i]['VENDEDOR']           = $key->VENDEDOR;
                $data[$i]['NOMBRE']             = $rVendedor[$index_key]->NOMBRE;               
                $data[$i]['META_RUTA']          = 'C$ ' . number_format($key->META_RUTA,0);
                $data[$i]['MesActual']          = 'C$ ' . number_format($key->MesActual, 0);

                foreach($SAC_into_vendedor as $zona){
                    if($zona["RUTA"] ==$key->VENDEDOR){
                        $data[$i]['ZONA'] = $zona["ZONA"];
                    }
                }

                $CUMPL_EJECT = ($key->META_RUTA=='0.00') ? number_format($key->META_RUTA,0) :  number_format(($key->MesActual / $key->META_RUTA) * 100,0) ;

                $data[$i]['RUTA_CUMPLI']        = $CUMPL_EJECT.' %';
                $data[$i]['CLIENTE']            = $key->CLIENTE;
                $data[$i]['META_CLIENTE']       = $key->META_CLIENTE;                
                $TENDENCIA = ($CUMPL_EJECT / $var_dias_habiles ) * $var_dias_factura ;
                $data[$i]['TENDENCIA']          = number_format($TENDENCIA,0) . " % ";
                $data[$i]['CLIENTE_COBERTURA']  = ($key->META_CLIENTE=='0.00') ? number_format($key->META_CLIENTE,0) : number_format(($key->CLIENTE / $key->META_CLIENTE) * 100,0).' %' ;
                $data[$i]['SKU']                = $key->SKU;
                $data[$i]['DS']                 = 'C$ ' . number_format($key->MesActual / $key->CLIENTE,0);                
                $data[$i]['DiaActual']          = 'C$ ' . number_format($key->DiaActual, 0);                
                $data[$i]['SaleWeek']          = 'C$ ' . number_format($key->SaleWeek, 0);                
                $data[$i]['EJEC']               = 'C$ ' . number_format($key->EJEC, 0);
                $data[$i]['SAC']                = 'C$ ' . number_format($key->SAC, 0);


                $Venta_Meta += $key->META_RUTA;
                $Venta_Real += $key->MesActual;
                $Venta_Actu += $key->DiaActual;
                $Venta_Week += $key->SaleWeek;

                $Cliente_Meta += $key->META_CLIENTE;
                $Cliente_Real += $key->CLIENTE;

                $i++;
            }
            
            $Venta_Porc = ($Venta_Meta=='0.00') ? number_format($Venta_Meta,2) :  number_format(($Venta_Real / $Venta_Meta) * 100,0);

            $data['Venta_Meta'] = $Venta_Meta;
            $data['Venta_Real'] = $Venta_Real;
            $data['Venta_Porc'] = $Venta_Porc;
            $data['Venta_Actu'] = $Venta_Actu;

            $Cliente_Porc = ($Cliente_Meta=='0.00') ? number_format($Cliente_Meta,2) :  number_format(($Cliente_Real / $Cliente_Meta) * 100,0);

            $data['Cliente_Meta'] = $Cliente_Meta;
            $data['Cliente_Real'] = $Cliente_Real;
            $data['Cliente_Porc'] = $Cliente_Porc;

            $data['Venta_Week'] = $Venta_Week;
            $data['Venta_Week_Label'] = Estadisticas::getLabelActual();
            

            
        }

        return $data;
    }

    public static function dtaVentasMes($d1,$d2){

        $data = array();
        $i=0;
        $sql_exec = '';
        

        if (Session::get('rol') == '9' || Session::get('rol') == '13') {
            $id_user = Auth::id();            
            $data_ruta = Estadisticas::get_rutas_group($id_user);
            $Rutas = " AND VENDEDOR IN ( ".$data_ruta['ruta']." ) ";
        } else {
            $Rutas = '';
        }

        
        $w = date("W", strtotime($d2));
        $y = date("Y", strtotime($d2));



        $sql_exec = "SELECT DATEPART(day, T0.FECHA_PEDIDO ) nDAY ,SUM(TOTAL_LINEA) TOTAL_DAY FROM PRODUCCION.dbo.view_master_pedidos_umk_v2 T0 	            
                    WHERE T0.FECHA_PEDIDO BETWEEN '".$d1."' AND '".$d2."' AND T0.VENDEDOR NOT IN ( 'F01', 'F12' )  ".$Rutas." GROUP BY T0.FECHA_PEDIDO ";

        $query = DB::connection('sqlsrv')->Select($sql_exec);


        if(count($query)>0 ) {
            foreach ($query as $key) {
                $data[$i]['DIA']    = $key->nDAY;
                $data[$i]['MONTO']  = $key->TOTAL_DAY;;               
                $i++;
            }            
        }

        return $data;
    }
    public static function dtaVentasRutas($d1,$d2){

        $data = array();
        $i=0;
        $sql_exec = '';
        

        if (Session::get('rol') == '9' || Session::get('rol') == '13') {
            $id_user = Auth::id();            
            $data_ruta = Estadisticas::get_rutas_group($id_user);
            $Rutas = " AND VENDEDOR IN ( ".$data_ruta['ruta']." ) ";
        } else {
            $Rutas = '';
        }

        
        $w = date("W", strtotime($d2));
        $y = date("Y", strtotime($d2));

        $SAC_into_vendedor = array(
            ["RUTA" => "F03","SAC" => "AURA","ZONA" => "MGA ABAJO NORTE"],
            ["RUTA" => "F05","SAC" => "AURA","ZONA" => "MGA ARRIBA"],
            ["RUTA" => "F21","SAC" => "AURA","ZONA" => "N/D"],
            ["RUTA" => "F19","SAC" => "NADIESKA","ZONA" => "OCCIDENTE"],
            ["RUTA" => "F06","SAC" => "NADIESKA","ZONA" => "LEON"],
            ["RUTA" => "F14","SAC" => "NADIESKA","ZONA" => "CHINANDEGA"],
            ["RUTA" => "F13","SAC" => "NADIESKA","ZONA" => "MGA ABAJO SUR"],
            ["RUTA" => "F07","SAC" => "YESSICA","ZONA" => "MYA-GDA"],
            ["RUTA" => "F23","SAC" => "YESSICA","ZONA" => "SUR ORIENTE"],
            ["RUTA" => "F09","SAC" => "REYNA","ZONA" => "EST-NS-MAD"],
            ["RUTA" => "F10","SAC" => "REYNA","ZONA" => "MAT-JIN"],
            ["RUTA" => "F22","SAC" => "REYNA","ZONA" => "N/D"],
            ["RUTA" => "F08","SAC" => "REYNA","ZONA" => "CAR-RIV"],
            ["RUTA" => "F11","SAC" => "YORLENI","ZONA" => "CHON-RSJ-RAAS"],
            ["RUTA" => "F20","SAC" => "YORLENI","ZONA" => "BOACO- RAAN"],
            ["RUTA" => "F02","SAC" => "ALEJANDRA","ZONA" => "INSTIT"],
            ["RUTA" => "F04","SAC" => "ALEJANDRA","ZONA" => "MCDO/MAYORISTAS"],
            ["RUTA" => "F15","SAC" => "","ZONA" => "VENTAS GERENCIA"],
            ["RUTA" => "F18","SAC" => "","ZONA" => ""],
        );


        $sql_exec = "SELECT T0.VENDEDOR ,SUM(TOTAL_LINEA) TOTAL_DAY FROM PRODUCCION.dbo.view_master_pedidos_umk_v2 T0 	            
                    WHERE T0.FECHA_PEDIDO BETWEEN '".$d1."' AND '".$d2."' AND T0.VENDEDOR NOT IN ( 'F01', 'F12' ) ".$Rutas." GROUP BY T0.VENDEDOR ORDER BY SUM(TOTAL_LINEA) DESC";

        $query = DB::connection('sqlsrv')->Select($sql_exec);

        $sql_vendedor = "SELECT VENDEDOR, NOMBRE FROM Softland.umk.VENDEDOR WHERE ACTIVO='S' AND VENDEDOR != 'LPCM'";
        $rVendedor = DB::connection('sqlsrv')->Select($sql_vendedor);

        if(count($query)>0 ) {
            foreach ($query as $key) {
                $data[$i]['VENDEDOR']   = $key->VENDEDOR;
                $data[$i]['MONTO']      = $key->TOTAL_DAY;
                $index_key = array_search($key->VENDEDOR, array_column($rVendedor, 'VENDEDOR'));
                $data[$i]['NOMBRE']     = $rVendedor[$index_key]->NOMBRE;  
                foreach($SAC_into_vendedor as $zona){
                    if($zona["RUTA"] ==$key->VENDEDOR){
                        $data[$i]['ZONA'] = $zona["ZONA"];
                    }
                }
                $i++;
            }            
        }

        return $data;
    }
}