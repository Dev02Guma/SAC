@extends('layouts.lyt_controlados')
@section('sac_title', 'Controlados')
@section('metodosjs')
    @include('Controlados.js_controlados')
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
                <h1 class="sac-h1">Controlados</h1>
                <p class="sac-sub">Reporte de medicamentos controlados</p>
              </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-9">  
                            <div class="input-group mb-3" >
                                <div class="input-group-text bg-grey" id="btn_search">
                                    <span class="fa fa-search fs--1 text-600" ></span>
                                </div>
                                <input class="form-control form-control-lg shadow-none search" type="search" placeholder="Buscar..." aria-label="search" id="tbl_search" name="tbl_search" />
                                <div class="input-group-text bg-success" id="button_export_excel">
                                    <span class="fa fa-file-excel fs--1 text-white" data-bs-toggle="tooltip" data-bs-placement="top" title="Exportar a Excel"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="input-group mb-3" >
                                <input class="form-control form-control-lg shadow-none" type="text" name="dt_range" id="dt_range" placeholder="Seleccionar Rango de Fechas" aria-label="search" />
                                <div class="input-group-text bg-grey" >
                                    <span class="fa fa-calendar fs--1 text-600" ></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="table-responsive fs--1">
                        <table class="mdl-data-table" id="tbl_facturas_controlados" class="display" style="width:100%"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection('content')