@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Usuarios')
@section('metodosjs')
@include('jsViews.js_usuario')
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
                <h1 class="sac-h1">Usuarios</h1>
                <p class="sac-sub">Roles y rutas asignadas</p>
              </div>
            </div>

            <div class="col-xxl-12">
              <div class="card h-100">
              <div class="card-header bg-light">
                <div class="row flex-between-center">
                    <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-0 mb-0 text-nowrap py-2 py-xl-0">Listas de Usuarios ( {{count($Usuarios)}} )</h5>
                    </div>
                    <div class="col-8 col-sm-auto text-end ps-2">
                        <div id="table-customers-replace-element">
                            <div class="input-group" >
                                <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar..." aria-label="search" id="id_txt_buscar_user" />
                                <div class="input-group-text bg-transparent">
                                    <span class="fa fa-search fs--1 text-600"></span>
                                </div>
                                <div class="input-group-text bg-transparent" id="id_btn_new">
                                    <span class="fa fa-plus fs--1 text-600"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="card-body p-0">
                  <div class="scrollbar">
                    <table class="table table-dashboard mb-0 table-borderless fs--1 border-200 overflow-hidden rounded-3 table-member-info" id="tbl_usuarios">
                      <thead style="display:none">
                        <tr>
                          <th></th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>

                    
                       
                        @foreach ($Usuarios as $usuario)
                        <tr class="border-bottom border-200">
                          <td>
                            <div class="d-flex align-items-center position-relative">
                              <div class="avatar avatar-2xl status-online">
                                <img class="rounded-circle" src="{{ asset('images/user/avatar-4.jpg') }}" />

                              </div>
                              <div class="flex-1 ms-3">
                                <h6 class="mb-0 fw-semi-bold"><a class="text-900" href="#!" onclick="OpenModal({{$usuario}})">{{ strtoupper($usuario->nombre) }} ( {{$usuario->RolName->descripcion}} )</a></h6>
                                <p class="text-500 fs--2 mb-0"><a class="text-700" href="#!" onclick="AsginarRuta({{$usuario}})">{{ strtoupper($usuario->username) }}</a></p>
                              </div>
                            </div>
                          </td>
                          
                          <td class="align-middle">
                            <div class="row g-2 justify-content-end">
                              <div class="col-auto">
                                @foreach ($usuario->Detalles as $Rutas)
                                    <a class="d-inline-flex align-items-center border rounded-pill px-3 py-1 me-2 mt-2 inbox-link" href="#!" onclick="Remover({{$Rutas}})">
                                        <span class="fas fa-user-tie text-danger" data-fa-transform="grow-4"></span><span class="ms-2"> {{$Rutas->RUTA}}</span>
                                    </a>
                                @endforeach
                              </div>
                            </div>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="card-footer bg-light py-2">
                  <div class="row flex-between-center">
                    <div class="col-auto">
                     
                    </div>
                    <div class="col-auto">
                        
                    </div>
                  </div>
                </div>
              </div>
            </div>

           
           
        </div>
    </div>
</main>
<div class="modal fade" id="modal_new_product" tabindex="-1">
          <div class="modal-dialog modal-xl">
            <div class="modal-content border-0">
            <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                <div class="position-relative z-index-1 light">
                    <h4 class="mb-0 text-white" id="authentication-modal-label">Informacion del Usuario</h4>                    
                    <p class="fs--1 mb-0 text-white" id="id_modal_state"> New </p>
                </div>
                <button class="btn-close btn-close-white position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
                <div class="modal-body p-card">
                    <div class="row g-2">
                        <div class="col-md-4 col-sm-12 col-xxl-4">
                            <div class="mb-3">
                                <label class="fs-0" for="id_nombre_usuario">Usuario</label>
                                <input class="form-control" id="id_nombre_usuario" type="text" name="title" required="required" />
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xxl-4">
                            <div class="mb-3">
                                <label class="fs-0" for="id_nombre_completo">Nombre Completo</label>
                                <input class="form-control" id="id_nombre_completo" type="text" name="title" required="required" />
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xxl-4">
                            <div class="mb-3">
                                <label class="fs-0" for="id_tipo_usuaro">Tipo de Usuario</label>
                                <select class="form-select" id="id_tipo_usuaro" name="label" required="required">
                                    <option value="" selected="selected">None</option>
                                    @foreach ($Roles as $Rol)
                                    <option value="{{$Rol->id}}">{{strtoupper($Rol->descripcion)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                    
                    </div>
                    <div class="mb-3">
                        <label class="fs-0" for="id_password">Contraseña</label>
                        <input class="form-control" id="id_password" type="password" name="title" required="required" />
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end align-items-center bg-light">
                    <button class="btn btn-danger px-4" id="id_remover" type="submit">Eliminar</button>
                    <button class="btn btn-primary px-4 ms-3" id="id_send_frm_produc" type="submit">Guardar</button>
                </div>
            </div>
          </div>
        </div>
@endsection('content')