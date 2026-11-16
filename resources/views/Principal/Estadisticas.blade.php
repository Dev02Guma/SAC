@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Estadísticas')
@section('metodosjs')
@include('jsViews.js_estadisticas')
@include('jsViews.js_ScheduleView')
@include('jsViews.js_skus')
@endsection
@section('content')
@php
  $cal = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>';
  $searchIco = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>';
  $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
@endphp

<main class="main" id="top">
  <div class="container-fluid" data-layout="container">
    <div class="content">

      @include('layouts.nav_importacion')

      <div class="sac-wrap">

        {{-- Encabezado + rango de fechas --}}
        <div class="sac-head">
          <div>
            <h1 class="sac-h1">Estadísticas</h1>
            <p class="sac-sub" id="sacEstSub">Venta, cumplimiento y plan de trabajo por vendedor</p>
          </div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <div id="sacEstLoading" class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
            <div class="sac-range2" role="group" aria-label="Rango de fechas">
              <div class="sac-range">
                {!! $cal !!}
                <label class="visually-hidden" for="sacEstDesde">Desde</label>
                <input type="text" class="form-control" id="sacEstDesde" placeholder="Desde" readonly>
              </div>
              <span class="sac-range2-sep" aria-hidden="true">–</span>
              <div class="sac-range">
                {!! $cal !!}
                <label class="visually-hidden" for="sacEstHasta">Hasta</label>
                <input type="text" class="form-control" id="sacEstHasta" placeholder="Hasta" readonly>
              </div>
              <input type="hidden" id="id_range_select">
            </div>
          </div>
        </div>

        {{-- Resumen del periodo --}}
        <section class="card sac-kpis sac-est-kpis" aria-label="Resumen del periodo">
          <div class="sac-kpi">
            <span class="sac-kl">Venta del periodo</span>
            <span class="sac-kv" id="id_Venta_Real">…</span>
            <span class="sac-muted">Meta <span id="id_Venta_Meta">…</span></span>
          </div>
          <div class="sac-kpi">
            <div class="sac-est-kl-row"><span class="sac-kl">Cumplimiento</span><span class="sac-tag sac-tag-neutral" id="sacEstRitmo" hidden></span></div>
            <span class="sac-kv" id="id_Venta_Porc">…</span>
            <div class="sac-bar sac-est-bar"><span id="sacEstBarVenta" style="width: 0%"></span><i class="sac-est-mark" id="sacEstMarkVenta" title="Óptimo: avance esperado a la fecha"></i></div>
            <span class="sac-muted">Óptimo <span id="sacEstEsperado">…</span></span>
          </div>
          <div class="sac-kpi">
            <span class="sac-kl">Clientes facturados</span>
            <span class="sac-kv"><span id="id_Cliente_Real">…</span> <span class="sac-kv-of">/ <span id="id_Cliente_Meta">…</span></span></span>
            <div class="sac-bar"><span id="sacEstBarCli" style="width: 0%; background: var(--sac-teal)"></span></div>
            <span class="sac-muted" id="sacEstCliNota"><span id="id_Cliente_Porc">…</span> de la meta</span>
          </div>
          <div class="sac-kpi">
            <span class="sac-kl">Mes transcurrido</span>
            <span class="sac-kv" id="id_Dias_porcent">…</span>
            <div class="sac-bar"><span id="sacEstBarDias" style="width: 0%; background: var(--sac-faint)"></span></div>
            <span class="sac-muted">Día <span id="id_Dias_Habiles">0</span> de <span id="id_Dias_Facturados">0</span> hábiles</span>
          </div>
          <div class="sac-kpi">
            <span class="sac-kl">Venta de hoy</span>
            <span class="sac-kv" id="id_Venta_Actu">…</span>
            <span class="sac-muted">Semana <strong class="sac-est-strong" id="id_Venta_Week">…</strong></span>
            <span class="sac-muted" id="id_Venta_Week_Label"></span>
          </div>
        </section>

        {{-- Detalle por pestañas --}}
        <section class="card">
          <ul class="nav nav-tabs sac-tabs-line" id="crm-revenue-chart-tab" role="tablist" data-tab-has-echarts="data-tab-has-echarts">
            <li class="nav-item" role="presentation"><a class="nav-link active" id="crm-revenue-tab" data-bs-toggle="tab" href="#crm-revenue" role="tab" aria-controls="crm-revenue" aria-selected="true">Ventas por día</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" id="sale-path-tab" data-bs-toggle="tab" href="#sale-path" role="tab" aria-controls="sale-path" aria-selected="false">Ventas por ruta</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" id="crm-profit-tab" data-bs-toggle="tab" href="#crm-profit" role="tab" aria-controls="crm-profit" aria-selected="false">Rutas <span class="sac-n" id="sacEstNRutas"></span></a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" id="crm-schedule-tab" data-bs-toggle="tab" href="#crm-schedule" role="tab" aria-controls="crm-schedule" aria-selected="false">Plan de trabajo</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" id="crm-skus-tab" data-bs-toggle="tab" href="#crm-skus" role="tab" aria-controls="crm-skus" aria-selected="false">SKUs 80/20</a></li>
          </ul>

          <div class="tab-content">

            {{-- Ventas por día --}}
            <div class="tab-pane active" id="crm-revenue" role="tabpanel" aria-labelledby="crm-revenue-tab">
              <div class="sac-est-chart-head">
                <div>
                  <h2 class="sac-card-title">Venta diaria</h2>
                  <p class="sac-muted mb-0" id="sacEstDiaRes">…</p>
                </div>
              </div>
              <div class="sac-est-chart">
                <div class="echart-sale-VentasDelMes" data-echart-responsive="true" data-echart-tab="data-echart-tab" style="height: 320px;"></div>
                <p class="sac-est-empty" id="sacEstDiaVacio" hidden>No hay ventas en este periodo</p>
              </div>
            </div>

            {{-- Ventas por ruta --}}
            <div class="tab-pane" id="sale-path" role="tabpanel" aria-labelledby="sale-path-tab">
              <div class="sac-est-chart-head">
                <div>
                  <h2 class="sac-card-title">Venta por ruta</h2>
                  <p class="sac-muted mb-0" id="sacEstRutaRes">…</p>
                </div>
              </div>
              <div class="sac-est-chart">
                <div class="echart-sale-VentasMesRutas" data-echart-responsive="true" data-echart-tab="data-echart-tab" style="height: 320px;"></div>
                <p class="sac-est-empty" id="sacEstRutaVacio" hidden>No hay ventas en este periodo</p>
              </div>
            </div>

            {{-- Rutas --}}
            <div class="tab-pane" id="crm-profit" role="tabpanel" aria-labelledby="crm-profit-tab">
              <div class="sac-toolbar">
                <div class="input-group" style="max-width: 300px; flex: 1 1 220px">
                  <span class="input-group-text">{!! $searchIco !!}</span>
                  <input class="form-control form-control-sm shadow-none" type="search" placeholder="Buscar ruta, vendedor o zona…" aria-label="Buscar ruta" id="sacEstBuscarRuta">
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                  <label class="sac-n mb-0" for="sacEstOrden">Ordenar por</label>
                  <select class="form-select form-select-sm" id="sacEstOrden" style="width: auto">
                    <option value="cumpl">Cumplimiento</option>
                    <option value="venta">Venta</option>
                    <option value="ruta">Ruta</option>
                  </select>
                </div>
              </div>
              <div class="sac-est-rutas" id="id_rutas" aria-live="polite"></div>
            </div>

            {{-- Plan de trabajo --}}
            <div class="tab-pane" id="crm-schedule" role="tabpanel" aria-labelledby="crm-schedule-tab">
              <div class="sac-toolbar">
                <div class="d-flex align-items-center gap-1">
                  <button class="btn btn-falcon-default btn-sm sac-est-ico" type="button" data-event="prev" aria-label="Anterior" title="Anterior"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></button>
                  <button class="btn btn-falcon-default btn-sm sac-est-ico" type="button" data-event="next" aria-label="Siguiente" title="Siguiente"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg></button>
                  <button class="btn btn-falcon-default btn-sm" type="button" data-event="today" id="btnToday">Hoy</button>
                </div>
                <h2 class="sac-card-title calendar-title mb-0"></h2>
                <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                  <label class="visually-hidden" for="sclVendedor">Vendedor</label>
                  <select class="form-select form-select-sm" id="sclVendedor" style="width: auto; max-width: 280px">
                    @foreach ($Vendedor as $v)
                      <option value="{{$v->VENDEDOR}}" valor="{{$v->VENDEDOR}}">{{$v->VENDEDOR}} - {{ strtoupper($v->NOMBRE) }}</option>
                    @endforeach
                  </select>
                  <div class="dropdown font-sans-serif">
                    <button class="btn btn-falcon-default btn-sm dropdown-toggle dropdown-caret-none" type="button" id="email-filter" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                      <span data-view-title="data-view-title">Semana</span>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="email-filter">
                      <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="dayGridMonth">Mes<span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span></a>
                      <a class="active dropdown-item d-flex justify-content-between" href="#!" data-fc-view="timeGridWeek">Semana<span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span></a>
                      <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="timeGridDay">Día<span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span></a>
                      <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="listWeek">Lista<span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span></a>
                      <a class="dropdown-item d-flex justify-content-between" href="#!" data-fc-view="listYear">Año<span class="icon-check"><span class="fas fa-check" data-fa-transform="down-4 shrink-4"></span></span></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="calendar-outline" id="appCalendar"></div>
            </div>

            {{-- SKUs 80/20 --}}
            <div class="tab-pane" id="crm-skus" role="tabpanel" aria-labelledby="crm-skus-tab">
              <div class="sac-toolbar">
                <div class="input-group" style="max-width: 260px; flex: 1 1 200px">
                  <span class="input-group-text">{!! $searchIco !!}</span>
                  <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar SKU…" aria-label="Buscar SKU" id="txt_search">
                </div>
                <label class="visually-hidden" for="select_rows">Filas por página</label>
                <select class="form-select form-select-sm" id="select_rows" style="width: auto">
                  <option selected="selected" value="-1">Todas</option>
                  <option value="5">5</option>
                  <option value="10">10</option>
                </select>
                <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                  <label class="visually-hidden" for="id_num_mes">Mes</label>
                  <select class="form-select form-select-sm" id="id_num_mes" style="width: auto">
                    @foreach ($meses as $n => $m)
                      <option value="{{ $n + 1 }}" {{ ($n + 1) == date('n') ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                  </select>
                  <label class="visually-hidden" for="id_num_anno">Año</label>
                  <select class="form-select form-select-sm" id="id_num_anno" style="width: auto">
                    @for ($i = date('Y'); $i >= 2022; $i--)
                      <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                  </select>
                  <label class="visually-hidden" for="id_select_vendedor">Vendedor</label>
                  <select class="form-select form-select-sm" id="id_select_vendedor" style="width: auto; max-width: 260px">
                    @foreach ($Vendedor as $v)
                      <option value="{{$v->VENDEDOR}}" valor="{{$v->VENDEDOR}}">{{$v->VENDEDOR}} - {{ strtoupper($v->NOMBRE) }}</option>
                    @endforeach
                  </select>
                  <button type="button" class="btn btn-primary btn-sm" id="btn_buscar">{!! $searchIco !!} Consultar</button>
                </div>
              </div>
              <div class="sac-tblbox">
                <table id="dt_articulos" class="table sac-list" width="100%">
                  <thead>
                    <tr>
                      <th>Artículo</th>
                      <th>Descripción</th>
                      <th class="text-end">Meta</th>
                      <th class="text-end">Ejecutado</th>
                      <th class="text-end">Cumplimiento %</th>
                      <th class="text-end">Clientes</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>

          </div>
        </section>

      </div>
    </div>
  </div>

  <div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border">
        <div class="modal-content border">
          <div class="modal-header px-card bg-light border-bottom-0">
            <h5 class="modal-title">Visita: <span id="id_lbl_title_event"> </span></h5>
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
              <input class="form-control datetimepicker initTimers" id="timepicker_end" type="text" placeholder="H:i" data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":false}' />
            </div>
            <div class="mb-3">
              <label class="fs-0" for="eventDescription">Description</label>
              <textarea class="form-control" rows="3" name="description" id="eventDescription"></textarea>
            </div>
            <label class="fs-0" id="eventOrden">Pedido:</label><br>
            <div class="row col-12" id="ordenes"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>
@endsection('content')
