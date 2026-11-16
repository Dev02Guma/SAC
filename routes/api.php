<?php

use App\Http\Controllers\GmvApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Route::post('Pedidos', 'GmvApiController@Pedidos')->name('Pedidos');
Route::post('post_order', 'GmvApiController@post_order')->name('post_order');

//Rutas para vinetas
Route::get('get_vineta', 'GmvApiController@get_vineta')->name('get_vineta');
Route::post('post_order_vineta', 'GmvApiController@post_order_vineta')->name('post_order_vineta');
Route::get('get_liquidacion_vineta', 'GmvApiController@get_liquidacion_vineta')->name('get_liquidacion_vineta');
Route::put('del_order_vineta', 'GmvApiController@del_order_vineta')->name('del_order_vineta');

Route::get('get_shipping', 'GmvApiController@get_shipping')->name('get_shipping');

//Rutas para Recibos
Route::post('post_order_recibo', 'GmvApiController@post_order_recibo')->name('post_order_recibo');
Route::get('get_recibos_colector', 'GmvApiController@get_recibos_colector')->name('get_recibos_colector');
Route::get('del_recibo_colector', 'GmvApiController@del_recibo_colector')->name('del_recibo_colector');
Route::get('get_recibos_adjuntos/{Recibo}', 'GmvApiController@get_recibos_adjuntos')->name('get_recibos_adjuntos/{Recibo}');
Route::post('post_adjunto', 'GmvApiController@post_adjunto')->name('post_adjunto');
Route::get('CronCheckVisita', "GmvApiController@runCronCheckVisita")->name('CronCheckVisita');

//Rutas varias
Route::get('get_help', 'GmvApiController@get_help')->name('get_help');
Route::get('get_tax_currency', 'GmvApiController@get_tax_currency')->name('get_tax_currency');
Route::get('get_comentarios', 'GmvApiController@get_comentarios')->name('get_comentarios');
Route::get('get_comentarios_im/{Ruta}/{OrderBy}', 'GmvApiController@get_comentarios_im')->name('get_comentarios_im/{Ruta}/{OrderBy}');
Route::get('get_banner', 'GmvApiController@get_banner')->name('get_banner');
Route::get('get_news', 'GmvApiController@get_news')->name('get_news');
Route::get('get_detalle_factura', 'GmvApiController@get_detalle_factura')->name('get_detalle_factura');
Route::get('last_3m', 'GmvApiController@last_3m')->name('last_3m');

//Rutas categorías
Route::get('category_id', 'GmvApiController@category_id')->name('category_id');
Route::get('get_category', 'GmvApiController@get_category')->name('get_category');

Route::get('clients_id', 'GmvApiController@clients_id')->name('clients_id');
Route::get('get_perfil_user', 'GmvApiController@get_perfil_user')->name('get_perfil_user');

//Rutas productos
Route::get('product_id', 'GmvApiController@product_id')->name('product_id');
Route::get('Articulos/{RUTA}', 'GmvApiController@Articulos')->name('Articulos/{RUTA}');
Route::get('get_stat_articulo', 'GmvApiController@get_stat_articulo')->name('get_stat_articulo');
Route::get('post_rpt_rutas', 'GmvApiController@post_rpt_rutas')->name('post_rpt_rutas');

Route::get('articulos_sin_facturar', 'GmvApiController@articulos_sin_facturar')->name('articulos_sin_facturar');
Route::get('post_usuario', 'GmvApiController@post_usuario')->name('post_usuario');
Route::post('post_update_datos', 'GmvApiController@post_update_datos')->name('post_update_datos');
Route::post('post_verificacion', 'GmvApiController@post_verificacion')->name('post_verificacion');
Route::get('get_nc', 'GmvApiController@get_nc')->name('get_nc');
Route::get('get_stat_ruta', 'GmvApiController@get_stat_ruta')->name('get_stat_ruta');

Route::post('post_report', 'GmvApiController@post_report')->name('post_report');
Route::post('post_add_comments_im', 'GmvApiController@post_add_comments_im')->name('post_add_comments_im');
Route::post('post_remove_post_im', 'GmvApiController@post_remove_post_im')->name('post_remove_post_im');
Route::post('remove_comments', 'GmvApiController@remove_comments')->name('remove_comments');
Route::post('push_pin', 'GmvApiController@push_pin')->name('push_pin');
Route::get('stac_recup', 'GmvApiController@stac_recup')->name('stac_recup');
Route::get('get_history_lotes', 'GmvApiController@get_history_lotes')->name('get_history_lotes');
Route::get('post_historico_factura', 'GmvApiController@post_historico_factura')->name('post_historico_factura');
Route::post('recibo_anular', 'GmvApiController@recibo_anular')->name('recibo_anular');

Route::get('getcomision/{RUTA}/{MONTH}/{YEAR}', 'GmvApiController@getcomision')->name('getcomision/{RUTA}/{MONTH}/{YEAR}');

Route::get('pedidos', 'GmvApiController@runInsertPedidos')->name('pedidos');
Route::get('plan_crecimiento/{RUTA}/{CLIENTE}', 'GmvApiController@plan_crecimiento')->name('plan_crecimiento/{RUTA}/{CLIENTE}');
Route::get('getHistoryItems/{RUTA}/{MONTH}/{YEAR}', 'GmvApiController@getHistoryItems')->name('getHistoryItems/{RUTA}/{MONTH}/{YEAR}');

Route::get('Verification', 'GmvApiController@runVerification')->name('Verification');

Route::post('CheckVisita', 'ScheduleController@CheckVisita')->name('CheckVisita');
Route::get('Schedule/{RUTA}/', 'ScheduleController@getSchedule')->name('Schedule/{RUTA}/');
Route::post('AddVisita', 'ScheduleController@AddVisita')->name('AddVisita');
Route::get('getDataVisita/{RUTA}/', 'ScheduleController@getDataVisita')->name('getDataVisita/{RUTA}/');
Route::post('UpdateVisita', 'ScheduleController@UpdateVisita')->name('UpdateVisita');
Route::get('rmVisita/{RUTA}', 'ScheduleController@rmVisita')->name('rmVisita/{RUTA}');
Route::post('reutilizar', 'ScheduleController@reutilizar')->name('reutilizar');

Route::get('Promociones', 'GmvApiController@Promociones')->name('Promociones');




Route::get('SendNotification', 'GmvApiController@SendNotification')->name('SendNotification');




