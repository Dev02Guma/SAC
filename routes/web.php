<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
Auth::routes();

Route::get('Home', 'HomeController@getHome')->name('Home');
Route::get('estadisticas', 'HomeController@getEstadistiacas')->name('estadisticas');
Route::get('getStats/{d1}/{d2}', 'HomeController@getStats')->name('getStats/{d1}/{d2}');
Route::get('/', 'Auth\LoginController@showLoginForm');
Route::get('/logout', 'Auth\LoginController@logout');
Route::get('ArticuloFavorito', 'HomeController@getArticuloFavorito')->name('ArticuloFavorito');
Route::get('getData', 'HomeController@getData')->name('getData');

//Rutas para promociones
Route::get('CalendarPromocion', 'HomeController@getCalendarPromocion')->name('CalendarPromocion');
Route::get('getDataPromocion', 'HomeController@getDataPromocion')->name('getDataPromocion');
Route::post('/insert_promocion', 'HomeController@insert_promocion')->name('insert_promocion');
Route::get('editPromocion/{id}/{activo}', 'HomeController@editPromocion')->name('editPromocion/{id}/{activo}');
Route::post('/update_promocion', 'HomeController@update_promocion')->name('update_promocion');


Route::get('dtaEstadisticas', 'HomeController@dtaEstadisticas')->name('dtaEstadisticas');


Route::get('getMiProgreso/{d1}/{d2}', 'HomeController@getMiProgreso')->name('getMiProgreso/{d1}/{d2}');
Route::get('getArticulosFavoritos', 'HomeController@getArticulosFavoritos')->name('getArticulosFavoritos');
Route::get('getDataArticulo/{ID}', 'HomeController@getDataArticulo')->name('getDataArticulo');
Route::post('AddFavs', 'HomeController@AddFavs')->name('AddFavs');
Route::get('getLotes/{BODEGA}/{ARTICULO}', 'HomeController@getLotes')->name('getLotes');
Route::get('getDetallesFactura/{ID}', 'HomeController@getDetallesFactura')->name('getDetallesFactura');

Route::get('getDataCliente', 'HomeController@getDataCliente')->name('getDataCliente');
Route::get('getDataCliente/{ID}', 'HomeController@getDataCliente')->name('getDataCliente');
Route::get('getProductosFacturados/{ID}', 'HomeController@getProductosFacturados')->name('getProductosFacturados');
Route::get('Comiciones', 'HomeController@Comiciones')->name('Comiciones');

Route::get('Usuarios', 'UsuarioController@getUsuarios')->name('Usuarios');
Route::post('SaveUsuario', 'UsuarioController@SaveUsuario')->name('SaveUsuario');
Route::post('DeleteUsuario', 'UsuarioController@DeleteUsuario')->name('DeleteUsuario');
Route::get('getRutas', 'UsuarioController@getRutas')->name('getRutas');

Route::post('AsignarRuta', 'UsuarioController@AsignarRuta')->name('AsignarRuta');
Route::post('RemoverRutaAsignada', 'UsuarioController@RemoverRutaAsignada')->name('RemoverRutaAsignada');

Route::post('ChancesStatus', 'HomeController@ChancesStatus')->name('ChancesStatus');
Route::post('getCommentPedido', 'HomeController@getCommentPedido')->name('getCommentPedido');
Route::post('AddCommentPedido', 'HomeController@AddCommentPedido')->name('AddCommentPedido');
Route::post('DeleteCommentPedido', 'HomeController@DeleteCommentPedido')->name('DeleteCommentPedido');
Route::post('getPedidosRangeDates', 'HomeController@getPedidosRangeDates')->name('getPedidosRangeDates');

Route::post('AddPlanCrecimiento', 'HomeController@AddPlanCrecimiento')->name('AddPlanCrecimiento');
Route::get('getPlanCrecimientoIco', 'HomeController@getPlanCrecimientoIco')->name('getPlanCrecimientoIco');

//Generar pdf y excel
Route::get('generarPDF', 'HomeController@generarPDF')->name('generarPDF');
Route::get('generarExcel', 'HomeController@generarExcel')->name('generarExcel');

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');


// RUTAS DEL LOGS DE VISITAS DEL MODULO
Route::get('monitoring', 'HomeController@monitoring')->name('monitoring');
Route::get('getmonitoring/{d1}/{d2}', 'HomeController@getmonitoring')->name('getmonitoring/{d1}/{d2}');

// ACTUALIZAR IMAGEN DEL ARTICULO
Route::post('imgArticulo', 'HomeController@imgArticulo')->name('imgArticulo');


Route::get('getDataVisita/{RUTA}/', 'ScheduleController@getDataVisita')->name('getDataVisita/{RUTA}/');
Route::post('UpdateVisita', 'ScheduleController@UpdateVisita')->name('UpdateVisita');
Route::get('Schedule', 'ScheduleController@getSchedule')->name('Schedule');
Route::Post('AddVisita', 'ScheduleController@AddVisita')->name('AddVisita');
Route::post('CheckVisita', 'ScheduleController@CheckVisita')->name('CheckVisita');

Route::get('rmVisita/{RUTA}', 'ScheduleController@rmVisita')->name('rmVisita/{RUTA}');


Route::get('Devoluciones', 'DevolucionesController@getDevoluciones')->name('Devoluciones');
Route::post('getDevoluciones', 'DevolucionesController@getData')->name('getDevoluciones');
Route::post('reutilizar', 'ScheduleController@reutilizar')->name('reutilizar');

Route::post('insertCuarentena', 'HomeController@insertCuarentena')->name('insertCuarentena');

Route::get('Controlados', 'ControladosController@getControlados')->name('Controlados');
Route::post('GetDataControlados', 'ControladosController@GetDataControlados')->name('GetDataControlados');
Route::get('ControladosExport/{Desde}/{Hasta}', 'ControladosController@ControladosExport')->name('ControladosExport');



