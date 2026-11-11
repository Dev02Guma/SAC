@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Agenda de visitas')
@section('metodosjs')
@include('jsViews.js_Schedule')
@endsection
@section('content')

<!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <div class="container-fluid " data-layout="container">
        <div class="content">
          @if(!request()->is('api*'))
              @include('layouts.nav_importacion')
              <div class="sac-head" style="margin-bottom: 20px">
                <div>
                  <h1 class="sac-h1">Agenda de visitas</h1>
                  <p class="sac-sub">Programa y da seguimiento a las visitas de la ruta</p>
                </div>
              </div>
          @endif
          <div class="card mb-3 overflow-hidden mt-3">
            <div class="card-header">
              <div class="row gx-0 align-items-center">
                <div class="col-auto d-flex justify-content-end order-md-1">
                <button class="btn icon-item icon-item-sm shadow-none p-0 me-1 ms-md-2" type="button" id="btnReutilizar" data-bs-toggle="tooltip" title="Reutilizar Visitas"><span class="fas fa-history"></span></button>
                  <button class="btn icon-item icon-item-sm shadow-none p-0 me-1 ms-md-2" type="button" data-event="prev" data-bs-toggle="tooltip" title="Previous"><span class="fas fa-arrow-left"></span></button>
                  <button class="btn icon-item icon-item-sm shadow-none p-0 me-1 me-lg-2" type="button" data-event="next" data-bs-toggle="tooltip" title="Next"><span class="fas fa-arrow-right"></span></button>
                </div>
                <div class="col-auto col-md-auto order-md-2">
                  <h4 class="mb-0 fs-0 fs-sm-1 fs-lg-2 calendar-title"></h4>
                </div>
                <div class="col col-md-auto d-flex justify-content-end order-md-3">
                  <button class="btn btn-falcon-primary btn-sm" type="button" data-event="today">Hoy</button>
                </div>
                
                <div class="col-md-auto d-md-none">
                  <hr />
                </div>
                <div class="col-auto d-flex order-md-0">
                  <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addEventModal"> <span class="fas fa-plus me-2"></span>Agendar</button>
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
              <div class="calendar-outline " id="appCalendar"></div>
            </div>
          </div>
        </div>
        <div class="modal fade" id="eventDetailsModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
              <div class="modal-content border">
                  <div class="modal-header px-card border-bottom-0 " id="id_modal_header">
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
                      <label class="fs-0" for="eventLabel" style="display: none">Visita fue:</label>
                      <select class="form-select" id="eventLabel" name="label" style="display: none">
                          <option value="0">Programada</option>
                          <option value="1">Efectiva</option>
                          <option value="2">No Efectiva</option>
                      </select>
                  </div>
                 
                  <div class="mb-3">
                      <label class="fs-0" for="eventStartDate" style="display: none">Hora Inicio: </label>
                      <input class="form-control" id="timepicker_ini" type="time" style="display: none"/>
                     

                  </div>
                  <div class="mb-3">
                      <label class="fs-0" for="eventEndDate" style="display: none">Hora Fin: </label>
                      <input class="form-control" id="timepicker_end" type="time"  style="display: none"/>
                  </div>
                  <div class="mb-3">
                      <label class="fs-0" for="eventDescription">Description</label>
                      <textarea class="form-control" rows="3" name="description" id="eventDescription"></textarea>
                  </div>
                  <label class="fs-0" id="eventOrden">Pedido:</label><br>
                  <div class="row col-12" id="ordenes">
                      
                  </div>
                  
                  </div>
                  <div class="card-footer d-flex justify-content-end align-items-center bg-light">
                  
                  <button class="btn btn-danger activo px-4 me-3" type="button" onclick="rmVisita()"> Remover</button>
                  <button class="btn btn-primary px-4" onclick="ConfirmedVisita()"  type="submit">Guardar</button>
                  </div>
              </div>
            </div>
          </div>
        </div>
       

        <div class="modal fade" id="addEventModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content border">
                <div class="modal-header px-card bg-light border-bottom-0">
                  <h5 class="modal-title">AGENDAR VISITA</h5>
                  <button class="btn-close me-n1" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-card">
                  <div class="mb-3">
                    <label class="fs-0" for="pArticulos">CLIENTES PARA <span id="id_ruta">{{$Ruta}}</span> : </label>
                    
                    <select class="form-select js-choice" id="sclCliente" size="1" name="organizerSingle" data-options='{"removeItemButton":true,"placeholder":true}'>
                      <option value="-1" valor="0" selected="selected">SELECCIONE</option>
                      @foreach ($Clientes as $c)
                        <option value="{{$c->CLIENTE}}" valor="{{$c->CLIENTE}}">{{strtoupper($c->NOMBRE)}}</option>
                      @endforeach
                      
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="fs-0" for="eventStartDate">Fecha de Inicio</label>
                    <input class="form-control datetimepicker" id="StartDate" type="text" required="required" name="startDate" placeholder="yyyy/mm/dd hh:mm" data-options='{"static":"true","enableTime":"true","dateFormat":"Y-m-d H:i"}' />
                  </div>
                 
                  <div class="mb-3">
                    <label class="fs-0" for="eventDescription">Descripción</label>
                    <textarea class="form-control" rows="3" name="description" id="Description" required="required"></textarea>
                  </div>
                </div>
                <div class="card-footer d-flex justify-content-end align-items-center bg-light">
                  <button class="btn btn-outline-primary" type="submit" id="AddVisita" >Guardar</button>
                </div>
            </div>
          </div>
        </div>


      </div>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

@endsection('content')
