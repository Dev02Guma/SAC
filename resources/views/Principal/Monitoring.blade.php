@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Monitoreo')
@section('metodosjs')
@include('jsViews.js_monitoring')
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
                <h1 class="sac-h1">Monitoreo</h1>
                <p class="sac-sub">Visitas registradas al módulo por usuario</p>
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
              </div>
            </div>


            

           
           
       
            <div class="row g-3">
            
              <div class="col-md-12 col-xxl-12">

              


                <div class="card">
                  
                  <div class="card-body">
                    <div class="scrollbar">
                    <table class="table table-dashboard mb-0 table-borderless fs--1 border-200 overflow-hidden rounded-3 table-member-info">
                      <thead class="bg-light">
                        <tr class="text-900">
                          <th>INFO RUTAS</th>
                          <th class="text-center">VISITAS COMISIONES</th>
                          <th class="text-center">VISITAS PLAN DE CRECIMIENTO</th>
                          <th class="text-center">VISITAS PEDIDOS</th>
                          <th class="text-center">TOTAL DE VISITAS</th>
                        </tr>
                      </thead>
                      <tbody id="id_filas">
                       
                        
                        
                      </tbody>
                    </table>
                  </div>
                  </div>
                </div>
              </div>
          </div>
    
          </div>
      
    </div>
</main>
@endsection('content')