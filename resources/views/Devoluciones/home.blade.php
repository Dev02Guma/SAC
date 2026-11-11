@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Devoluciones')
@section('metodosjs')
@include('jsViews.js_Devoluciones')
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
                <h1 class="sac-h1">Devoluciones</h1>
                <p class="sac-sub">Consulta de devoluciones por factura</p>
              </div>
            </div>

            <div class="d-flex justify-content-center mb-2">
                <div id="id_loading" class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div class="card mb-3">
                <div class="bg-holder d-none d-lg-block bg-card" >
                </div>
                <!--/.bg-holder-->

                <div class="card-body position-relative">
                
            
                <div>
                <div class="row justify-content-between">
                    <div class="col">
                      <div class="d-flex">                        
                        <div class="flex-1 align-self-center">
                          <p class="mb-1 lh-1"><a class="fw-semi-bold" href="#!" > <span id="lbl_nombre_cliente">  00000 - NOMBRE CLIENTE </span> </a> &bull; Ruta &bull; <a href="#!"> <span id="lbl_ruta"></span> </a> </p>
                          <h5>Factura &bull; <span id="lbl_factura"> 0000 </span> </h5>
                          <span id="lbl_fecha_factura">Mes 01, 2000</span>
                        </div>
                      </div>
                    </div>
                  </div>
                    
                </div>
                </div>
            </div>
           
            <div class="card mb-3">
                <div class="card-body">
                <div class="input-group mb-3" >
                        <input class="form-control form-control-lg shadow-none search" type="search" placeholder="Numero de Factura" aria-label="search" id="tbl_buscar_tbl_facturas" />
                        <div class="input-group-text bg-grey" id="btn_search">
                            <span class="fa fa-search fs--1 text-600" ></span>
                        </div>
                    </div>
                <div class="table-responsive fs--1">
                    <table class="table border-bottom" id="tbl_facturas_decolucion">
                    <thead class="bg-200 text-900">
                        <tr>
                        <th class="border-0">ARTICULOS</th>
                        <th class="border-0 text-center">CANTIDAD</th>
                        <th class="border-0 text-end">LOTE</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        
                    </tbody>
                    </table>
                </div>
                
                </div>
            </div>
    </div>
</main>
@endsection('content')