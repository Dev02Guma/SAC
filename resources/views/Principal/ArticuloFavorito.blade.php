@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Artículos favoritos')
@section('metodosjs')
@include('jsViews.js_ArticuloFavorito')
@endsection
@section('content')

<!-- ===============================================-->
<!--    Main Content-->
<!-- ===============================================-->
<main class="main" id="top">
    <div class="container-fluid" data-layout="container">
        <div class="content">
            @include('layouts.nav_importacion')
            @php
              $searchIco = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>';
            @endphp

            <div class="sac-wrap">
              <div class="sac-head">
                <div>
                  <h1 class="sac-h1">Artículos favoritos</h1>
                  <p class="sac-sub">Marca con la estrella los artículos que verá la fuerza de venta en Inventario</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div id="id_loading" class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
                </div>
              </div>

              <section class="card" style="overflow: hidden" aria-label="Inventario y favoritos">
                <div class="sac-toolbar">
                  <div class="d-flex flex-wrap gap-2" role="group" aria-label="Filtrar artículos">
                    <button type="button" class="sac-chip on" data-fav-chip="todos" aria-pressed="true">Todos <span class="sac-n" id="sac_fav_n_todos">0</span></button>
                    <button type="button" class="sac-chip" data-fav-chip="fav" aria-pressed="false">Favoritos <span class="sac-n" id="sac_fav_n_fav">0</span></button>
                    <button type="button" class="sac-chip" data-fav-chip="nofav" aria-pressed="false">Sin marcar <span class="sac-n" id="sac_fav_n_nofav">0</span></button>
                  </div>
                  <div class="input-group ms-auto" style="max-width: 320px; flex: 0 1 320px">
                    <span class="input-group-text">{!! $searchIco !!}</span>
                    <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar artículo o código…" aria-label="Buscar en inventario" id="tbl_buscar_inventario" />
                  </div>
                  <label class="d-flex align-items-center gap-2 sac-muted">
                    <select class="form-select form-select-sm" style="width: auto" id="tbl_select_inventario" aria-label="Filas por página">
                      @foreach ([10 => '10', 20 => '20', 50 => '50', -1 => 'Todas'] as $v => $t)<option value="{{ $v }}" @if($v == 10) selected @endif>{{ $t }}</option>@endforeach
                    </select>
                  </label>
                </div>
                <div class="sac-tblbox"><table id="tbl_inventario" class="table overflow-hidden" style="width:100%"></table></div>
              </section>
            </div>
        </div>
    </div>
</main>
@endsection('content')
