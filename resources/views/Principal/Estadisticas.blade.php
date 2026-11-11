@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Estadísticas')
@section('metodosjs')
@include('jsViews.js_estadisticas')
@include('jsViews.js_ScheduleView')
@include('jsViews.js_skus')
@endsection
@section('content')

<!-- ===============================================-->
<!--    Main Content-->
<!-- ===============================================-->
<main class="main" id="top">
    <div class="container-fluid" data-layout="container">        
        <div class="content">            

            @include('layouts.nav_importacion')
            <div class="sac-head" style="margin-bottom: 20px">
              <div>
                <h1 class="sac-h1">Estadísticas</h1>
                <p class="sac-sub">Venta, cumplimiento y plan de trabajo por vendedor</p>
              </div>
            </div>

            <div class="col-md-12 col-xxl-12 mb-3">
              <div class="card h-100">
                
                <div class="card-header">
                  <div class="d-flex position-relative align-items-center">
                    <div class="flex-1">
                      <div class="d-flex flex-between-center">
                      <a class="btn btn-link btn-sm px-0 shadow-none" href="Home"><span class="fas fa-arrow-left ms-1 fs--2"></span> Regresar</a>
                    <div class="row flex-between-center">
                            <div class="col-4 col-sm-auto d-flex align-items-center">
                                                      
                            </div>
                            <div class="col-8 col-sm-auto text-end ">
                              <div class="row g-3 needs-validation" >
                              
                                <div class="col-md-auto">
                                  <div class="input-group" >
                                  <form class="row align-items-center g-3">
                                    <div class="col-auto"><h6 class="text-700 mb-0"> </h6></div>
                                    <div class="col-md-auto position-relative">                                      
                                      <input id="id_range_select" class="form-control form-control-sm datetimepicker" type="text" data-options='{"mode":"range","dateFormat":"Y-m-d","disableMobile":true}'/>
                                    </div>
                                  </form>  
                                      <div class="input-group-text bg-transparent">
                                          <span class="fa fa-calendar fs--1 text-600"></span>
                                      </div>
                                      <div class="input-group-text bg-transparent" id="id_btn_new">
                                          <span class="fas fa-history fs--1 text-600"></span>
                                      </div>
                                      
                                    </div>
                                  </div>
                              </div>
                            </div>
                          </div>
                  </div>
                    </div>
                  </div>
                 
                  
                </div>                
                <div class="card-body p-0">
                  <div class="scrollbar-overlay pt-0 px-card ask-analytics">
                    <div class="row g-3 mb-3">

                      <div class="col-md-4 col-xxl-4">
                        <div class="border border-1 border-300 rounded-2 p-3 ask-analytics-item position-relative">
                    
                            <div class="col-auto">
                              <div class="row g-sm-4">
                                
                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-4 border-sm-end border-200">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fab fa-font-awesome-flag text-primary"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Venta_Meta" >...</h6>
                                        <p class="mb-0 fs--2 text-500">Meta del mes.</p>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-4 border-sm-end border-200">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-dollar-sign text-success"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Venta_Real" >...</h6>
                                        <p class="mb-0 fs--2 text-500">Venta del mes.</p>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-0">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-chart-bar text-info"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Venta_Porc" >...</h6>
                                        <p class="mb-0 fs--2 text-500">% CUMPL.</p>
                                      </div>
                                  </div>
                                </div>
                        
                              </div>
                            </div>
                        </div>
                      </div>
                     
                      <div class="col-md-4 col-xxl-4">
                        <div class="border border-1 border-300 rounded-2 p-3 ask-analytics-item position-relative">
                      
                            <div class="col-auto">
                              <div class="row g-sm-4">

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-4 border-sm-end border-200">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-dollar-sign text-primary"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Venta_Actu" >...</h6>
                                        <p class="mb-0 fs--2 text-500">Ventas del Dia.</p>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-4 border-sm-end border-200">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-dollar-sign text-success"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0" id="id_Venta_Week">...</h6>
                                        <p class="mb-0 fs--2 text-500" id="id_Venta_Week_Label">Ventas del 0 al 0 de --- </p>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-0">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-chart-bar text-info"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0" id="id_Dias_porcent">...</h6>
                                        <p class="mb-0 fs--2 text-500" id="id_Venta_Week_Label"><span id="id_Dias_Habiles">0</span> de <span id="id_Dias_Facturados"> 0 </span> Dias Hábiles.</p>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </div>
                      </div>
                      <div class="col-md-4 col-xxl-4">
                        <div class="border border-1 border-300 rounded-2 p-3 ask-analytics-item position-relative">
                     
                            <div class="col-auto">
                              <div class="row g-sm-4">

                              
                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-4 border-sm-end border-200">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-user-friends text-primary"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Cliente_Meta" >...</h6>
                                        <p class="mb-0 fs--2 text-500">Meta de Clientes.</p>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-4 border-sm-end border-200">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-user-friends text-success"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Cliente_Real" >...</h6>
                                        <p class="mb-0 fs--2 text-500">Clientes Fact.</p>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 col-sm-auto">
                                  <div class="d-flex align-items-center pe-0">
                                    <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-chart-bar text-info"></span></div>
                                      <div class="flex-1">
                                        <h6 class="text-800 mb-0"id="id_Cliente_Porc" >...</h6>
                                        <p class="mb-0 fs--2 text-500">% Cumpl.</p>
                                    </div>
                                  </div>
                                </div>

                              </div>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            

           
           
       
            <div class="row g-3">           
              <div class="col-md-12 col-xxl-12">
                <div class="card">
                  <div class="card-header d-flex flex-between-center ps-0 py-0 border-bottom">
                    <ul class="nav nav-tabs border-0 flex-nowrap tab-active-caret" id="crm-revenue-chart-tab" role="tablist" data-tab-has-echarts="data-tab-has-echarts">
                      <li class="nav-item" role="presentation"><a class="nav-link py-3 mb-0 active" id="crm-revenue-tab" data-bs-toggle="tab" href="#crm-revenue" role="tab" aria-controls="crm-revenue" aria-selected="true">Ventas por Dia</a></li>                    
                      <li class="nav-item" role="presentation"><a class="nav-link py-3 mb-0" id="sale-path-tab" data-bs-toggle="tab" href="#sale-path" role="tab" aria-controls="sale-path" aria-selected="true">Ventas por Ruta</a></li>
                      <li class="nav-item" role="presentation"><a class="nav-link py-3 mb-0" id="crm-profit-tab" data-bs-toggle="tab" href="#crm-profit" role="tab" aria-controls="crm-profit" aria-selected="false">Mis Rutas</a></li>
                      <li class="nav-item" role="presentation"><a class="nav-link py-3 mb-0" id="crm-schedule-tab" data-bs-toggle="tab" href="#crm-schedule" role="tab" aria-controls="crm-schedule" aria-selected="false">Plan de trabajo</a></li>
                      
                      <li class="nav-item" role="presentation"><a class="nav-link py-3 mb-0" id="crm-skus-tab" data-bs-toggle="tab" href="#crm-skus" role="tab" aria-controls="crm-skus" aria-selected="false">SKUs 80/20</a></li>
                    </ul>                 
                  </div>
                  <div class="card-body">
                    <div class="row g-1">                    
                      <div class="col-xxl-12">
                        <div class="tab-content">

                          <div class="tab-pane active" id="crm-revenue" role="tabpanel" aria-labelledby="crm-revenue-tab">
                            <div class="echart-sale-VentasDelMes" data-echart-responsive="true" data-echart-tab="data-echart-tab" style="height:320px;"></div>
                          </div>

                          <div class="tab-pane" id="sale-path" role="tabpanel" aria-labelledby="sale-path-tab">
                            <div class="echart-sale-VentasMesRutas" data-echart-responsive="true" data-echart-tab="data-echart-tab" style="height:320px;"></div>
                          </div>

                          <div class="tab-pane" id="crm-profit" role="tabpanel" aria-labelledby="crm-profit-tab">
                            <div class="row fs--1 px-1 py-0" id="id_rutas"></div>
                          </div>

                          <div class="tab-pane" id="crm-schedule" role="tabpanel" aria-labelledby="crm-schedule-tab">

                            <div class="card mb-3 overflow-hidden">
                              <div class="card-header">
                                <div class="row align-items-center">

                                  <div class="col-auto d-flex justify-content-end order-md-1">
                                    <button class="btn icon-item icon-item-sm shadow-none p-0 me-1 ms-md-2" type="button" data-event="prev" data-bs-toggle="tooltip" title="Previous"><span class="fas fa-arrow-left"></span></button>
                                    <button class="btn icon-item icon-item-sm shadow-none p-0 me-1 me-lg-2" type="button" data-event="next" data-bs-toggle="tooltip" title="Next"><span class="fas fa-arrow-right"></span></button>
                                  </div>

                                  <div class="col-auto col-md-auto order-md-2">
                                    <h4 class="mb-0 fs-0 fs-sm-1 fs-lg-2 calendar-title"></h4>
                                  </div>
                                  
                                  
                                  <div class="col col-md-auto d-flex justify-content-end order-md-3">
                                    <button class="btn btn-falcon-primary btn-sm" type="button" data-event="today" id="btnToday">Hoy</button>
                                  </div>

                                  <div class="col col-md-auto d-flex justify-content-end order-md-3">
                                    <select class="form-select " id="sclVendedor" >
                                      @foreach ($Vendedor as $v)
                                        <option value="{{$v->VENDEDOR}}" valor="{{$v->VENDEDOR}}">{{$v->VENDEDOR}} - {{  strtoupper($v->NOMBRE)}}</option>
                                      @endforeach
                                      
                                    </select>
                                  
                                  </div>
                                
                                  <div class="col d-flex justify-content-end order-md-2">
                                    <div class="dropdown font-sans-serif me-md-2">
                                      <button class="btn btn-falcon-default text-600 btn-sm dropdown-toggle dropdown-caret-none" type="button" 
                                      id="email-filter" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                          <span data-view-title="data-view-title">Semana</span><span class="fas fa-sort ms-2 fs--1"></span>
                                      </button>
                                      <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="email-filter">
                                          <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="dayGridMonth">Mes
                                              <span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span>
                                          </a>
                                          <a class="active dropdown-item d-flex justify-content-between" href="#!" data-fc-view="timeGridWeek">Semana
                                              <span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span>
                                          </a>
                                          <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="timeGridDay">Día
                                              <span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span>
                                          </a>
                                          <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="listWeek">Lista
                                              <span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span>
                                          </a>
                                          <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="listYear">Año
                                              <span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span>
                                          </a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="card-body p-0">
                                <div class="calendar-outline" id="appCalendar"></div>
                              </div>
                            </div>
                            

                            
                          </div>

                          <div class="tab-pane" id="crm-skus" role="tabpanel" aria-labelledby="crm-skus-tab">
                            <div class="card-header">
                              <div class="row align-items-center">
                              <div class="col-auto d-flex justify-content-end order-md-1">

                              

                                <div class="row gy-2 gx-3 align-items-center">
                                  <div class="col-auto">
                                    <label class="visually-hidden" for="autoSizingInput">Name</label>
                                    <div class="input-group">
                                      <div class="input-group-text bg-transparent"><span class="fa fa-search fs--1 text-600"></span></div>
                                      <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar SKU" aria-label="search" id="txt_search" />
                                    </div>
                                  </div>
                                  <div class="col-auto">
                                    <label class="visually-hidden" for="autoSizingSelect">Preference</label>
                                    <select class="form-select" id="select_rows">
                                      <option selected="selected" value="-1">Todos</option>
                                      <option value="5">5</option>
                                      <option value="10">10</option>
                                    </select>
                                  </div>
                                  </div>


                                  
                                </div>
                                <div class="col col-md-auto d-flex justify-content-end order-md-3 mb-3">

                                  <div class="row g-3">
                                    <div class="col-md-3">
                                      <label class="form-label" for="inputState">Mes</label>
                                      <select class="form-select" id="id_num_mes">    
                                        <option value="1">Ene</option>
                                        <option value="2">Feb</option>
                                        <option value="3">Mar</option>
                                        <option value="4">Abr</option>
                                        <option value="5">May</option>
                                        <option value="6">Jun</option>
                                        <option value="7">Jul</option>
                                        <option value="8">Ago</option>
                                        <option value="9">Sep</option>
                                        <option value="10">Oct</option>
                                        <option value="11">Nov</option>
                                        <option value="12">Dic</option>
                                      </select>
                                    </div>
                                    <div class="col-md-3">
                                      <label class="form-label" for="inputAno">Año</label>
                                      <select class="form-select" id="id_num_anno">    
                                        @for ($i = date('Y'); $i >= 2022; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                      </select>
                                    </div>
                                    
                                      <div class="col-6">
                                        <label class="form-label" for="inputZip">Vendedor</label>
                                        <div class="input-group">
                                        <select class="form-select " id="id_select_vendedor" >
                                          @foreach ($Vendedor as $v)
                                            <option value="{{$v->VENDEDOR}}" valor="{{$v->VENDEDOR}}">{{$v->VENDEDOR}} - {{  strtoupper($v->NOMBRE)}}</option>
                                          @endforeach
                                        </select>
                                        <div class="input-group-text btn-primary" id="btn_buscar"><span class="fa fa-search fs--1 text-white"></span></div>
                                      </div>
                                    </div>
                                    </div>
                                  
                                </div>
                                <div class="col d-flex justify-content-end order-md-2">
                                  
                                </div>
                            </div>
                            <div class="card-body p-0">
                              
                            </div>

                            <table id="dt_articulos" class="display" width="100%">
                                <thead>
                                  <tr>
                                    <th>ARTICULO</th>
                                    <th>DESCRIPCION</th>
                                    <th>META</th>    
                                    <th>EJECUTADO</th>
                                    <th>CUMPLIMIENTO %</th>
                                    <th>CLIENTES</th>
                                  </tr>
                                </thead>
                              </table>
                          </div>

                          </div>


                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>
    
          </div>
          
    </div>

    <div class="modal fade" id="eventDetailsModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
              <div class="modal-content border">
                  <div class="modal-header px-card bg-light border-bottom-0">
                    <h5 class="modal-title">Visita:  <span id="id_lbl_title_event"> </span></h5>
                    <span id="id_event" style="display:none">0</span>
                  <button class="btn-close me-n1" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-card">
                  <div class="mb-3">
                      <label class="fs-0" for="NameClient">Nombre de Cliente</label>
                      <input class="form-control" id="NameClient" type="text" name="title" />
                  </div>
                  <div class="mb-3">
                      <label class="fs-0" for="eventLabel" style="display: none;">Visita fue:</label>
                      <select class="form-select" id="eventLabel" name="label">
                          <option value="0">N/D</option>
                          <option value="1">Efectiva</option>
                          <option value="2">No Efectiva</option>
                      </select>
                  </div>
                  <div class="mb-3">
                      <label class="fs-0" for="eventStartDate" style="display: none;">Hora Inicio</label>
                      <input class="form-control datetimepicker initTimers" id="timepicker_ini" type="text" placeholder="H:i" data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":false}' />
                  </div>
                  <div class="mb-3">
                      <label class="fs-0" for="eventEndDate" style="display: none;">Hora Fin</label>
                      <input class="form-control datetimepicker initTimers" id="timepicker_end"  type="text" placeholder="H:i" data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":false}' />
                  </div>
                  <div class="mb-3">
                      <label class="fs-0" for="eventDescription">Description</label>
                      <textarea class="form-control" rows="3" name="description" id="eventDescription"></textarea>
                  </div>
                  <label class="fs-0" id="eventOrden">Pedido:</label><br>
                  <div class="row col-12" id="ordenes">
                      
                  </div>
                  
                  </div>
              </div>
            </div>
          </div>
        </div>
       
</main>
@endsection('content')