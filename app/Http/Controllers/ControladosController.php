<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Controlados;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style;
use PHPExcel_Style_Border;

class ControladosController extends Controller {
    public function __construct()
    {
        $this->middleware('auth');
    } 
    public function getControlados() {
        return view('Controlados.Home');
    }
    public function GetDataControlados(Request $request) {

        $data = Controlados::getData($request);

        return response()->json([
            'Facturas' => [
                'Controlados' => $data
            ]
        ]);
    }
    public function ControladosExport( $Desde, $Hasta) {
        

        $rows = Controlados::whereBetween('Fecha_de_factura', [$Desde, $Hasta])
            ->where('ANULADA', 'N') 
            ->where('BODEGA', '005')
            ->get();

        $objPHPExcel = new PHPExcel();

        $titulosColumnas = array(
            'Fecha',
            'Factura',
            'Artículo',
            'Descripción',
            'Cantidad',
            'Lote',
            'Bodega',
            'Anulada',
            'Cliente Código',
            'Nombre Cliente',
            'Precio Unitario',
            'Venta Total'
        );
        /* ===== ENCABEZADOS ===== */

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $titulosColumnas[0])
            ->setCellValue('B1', $titulosColumnas[1])
            ->setCellValue('C1', $titulosColumnas[2])
            ->setCellValue('D1', $titulosColumnas[3])
            ->setCellValue('E1', $titulosColumnas[4])
            ->setCellValue('F1', $titulosColumnas[5])
            ->setCellValue('G1', $titulosColumnas[6])
            ->setCellValue('H1', $titulosColumnas[7])
            ->setCellValue('I1', $titulosColumnas[8])
            ->setCellValue('J1', $titulosColumnas[9])
            ->setCellValue('K1', $titulosColumnas[10])
            ->setCellValue('L1', $titulosColumnas[11]);

        /* ===== DATA ===== */

        $i = 2;

        foreach ($rows as $row) {

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, date('Y-m-d', strtotime($row->Fecha_de_factura)))
                ->setCellValue('B'.$i, $row->FACTURA)
                ->setCellValue('C'.$i, $row->ARTICULO)
                ->setCellValue('D'.$i, strtoupper($row->DESCRIPCION))
                ->setCellValue('E'.$i, $row->CANTIDAD_FACT)
                ->setCellValue('F'.$i, $row->LOTE)
                ->setCellValue('G'.$i, $row->BODEGA)
                ->setCellValue('H'.$i, $row->ANULADA)
                ->setCellValue('I'.$i, $row->CLIENTE_CODIGO)
                ->setCellValue('J'.$i, strtoupper($row->Nombre_Cliente))
                ->setCellValue('K'.$i, $row->PRECIO_UNITARIO)
                ->setCellValue('L'.$i, $row->venta_total);

            $i++;
        }

        /* ===== ESTILOS ===== */

        $sheet = $objPHPExcel->getActiveSheet();

        $estiloHeader = array(
            'font' => array('bold' => true),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $sheet->getStyle('A1:L1')->applyFromArray($estiloHeader);

        // /* ===== FORMATOS NUMÉRICOS ===== */

        $sheet->getStyle('E2:E'.($i-1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->getStyle('K2:L'.($i-1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        /* ===== TAMAÑOS DE COLUMNAS ===== */

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(30);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);

        /* ===== MEJORAS VISUALES PRO ===== */

        $sheet->setTitle('CONTROLADOS');

        /* ===== DESCARGA ===== */

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="Controlados '.$Desde.' al '.$Hasta.'.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
    }

}