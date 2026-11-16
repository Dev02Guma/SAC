<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InsertarExcepciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmv:insertar-excepciones {fecha? : FechaInicio YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el stored procedure sp_gmv_insertar_excepciones en SQLSRV';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fecha = $this->argument('fecha') ?? date('Y-m-d');

        try {
            // EXEC sp_gmv_insertar_excepciones @FechaInicio = '2026-02-01';


            //DB::connection('sqlsrv')->statement('SET NOCOUNT ON; EXEC sp_gmv_masterArticulos_update');            
            // RECUPERA TODO LO FACTURADO DE EXPANSION Y LO INSERTA EN LA TABLA DE EXCEPCIONES PARA QUE SE PUEDA CONSULTAR DESDE GMV
            DB::connection('sqlsrv')->statement('SET NOCOUNT ON; EXEC PRODUCCION.dbo.sp_gmv_insertar_excepciones @FechaInicio = ?', [$fecha]);


            $this->info("Stored procedure ejecutado correctamente para FechaInicio={$fecha}");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al ejecutar el stored procedure: ' . $e->getMessage());
            return 1;
        }
    }
}
