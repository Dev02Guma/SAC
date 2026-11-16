<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Visita;
use Illuminate\Support\Facades\Auth;
use App\Models\ClientesFull;

class ScheduleController extends Controller
{
    public function CalcVinneta() 
    {
        DB::connection('sqlsrv')->select('SET NOCOUNT ON; EXEC PRODUCCION.dbo.fn_vinnetas_calc ');

        \Log::channel('Schedule')->info("Actualizacion de Vinetas");

    }
    public function RunPedidos()
    {
        $url = route('pedidos');
        $client = new Client(['verify' => false]);
        $client->get($url);
        //\Log::channel('Schedule_pedidos')->info("Ejecucion de Tarea de Pedidos ");

        
    }
    public function CronCheckVisita()  {

        $url = route('CronCheckVisita');
        $Cron = new Client(['verify' => false]);
        $Cron->get($url);
    }

    public function ImportVerification()  {

        $url = route('Verification');
        $client = new Client(['verify' => false]);
        $client->get($url);

        //\Log::channel('Schedule')->info("Ejecucion de Tarea de Importacion de Clientes Verificados ");
    }

    public function getSchedule(Request $request, $ruta = null){
        
        $Ruta = $ruta ?? (Auth::user() ? Auth::user()->username : null);

        $Clientes      = ClientesFull::WHERE('VENDEDOR',$Ruta)->get();
        return view('Schedule.home', compact('Clientes','Ruta'));     

    }
    

    public function AddVisita(Request $request){

        $response = Visita::Add($request);
        
        return response()->json($response);

    }

    public function CheckVisita(Request $request)
    {
        $response = Visita::CheckVisita($request);
        
        return response()->json($response);
    }
    public function reutilizar(Request $request){

        $response = Visita::reutilizar($request);
        
        return response()->json($response);

    }

    
    public function getDataVisita($Ruta){

        $Visita = Visita::getData($Ruta);
        return response()->json($Visita);
    }

    public function UpdateVisita(Request $request){

        $Visita = Visita::UpdateVisita($request);
        return response()->json($Visita);

    }
    public function rmVisita($Id){

        $Visita = Visita::rmVisita($Id);
        return response()->json($Visita);

    }
}
