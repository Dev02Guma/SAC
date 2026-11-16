@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Usuarios')
@php
  // Grupos para el filtro y estilo de la etiqueta de rol
  $grupoRol = function ($idRol) {
      $idRol = (string) $idRol;
      if ($idRol === '1') return 'admin';
      if (in_array($idRol, ['2', '9'], true)) return 'sac';
      if ($idRol === '13') return 'sup';
      return 'otros';
  };
  $nombreRol = function ($u) {
      $mapa = ['1' => 'Admin', '2' => 'SAC Sup.', '9' => 'SAC', '10' => 'Normal', '11' => 'Schedule', '12' => 'Devolución', '13' => 'Supervisor', '14' => 'Controlados'];
      return $mapa[(string) $u->id_rol] ?? ucfirst(strtolower(optional($u->RolName)->descripcion ?? 'Sin rol'));
  };
  // "SAC02 | REYNA ALVAREZ" -> "Reyna Alvarez" (el código ya se muestra como usuario)
  $titulo = function ($t) {
      $t = trim((string) $t);
      if (mb_strpos($t, '|') !== false) { $partes = array_filter(array_map('trim', explode('|', $t))); $t = end($partes) ?: $t; }
      return mb_convert_case(mb_strtolower($t, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
  };
  $iniciales = function ($t) {
      $p = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\s]/u', ' ', (string) $t)), -1, PREG_SPLIT_NO_EMPTY);
      return mb_strtoupper(mb_substr($p[0] ?? '?', 0, 1) . mb_substr($p[1] ?? '', 0, 1)) ?: '?';
  };

  // Datos para el modal (sin contraseñas)
  $datosUsuarios = $Usuarios->map(function ($u) use ($grupoRol, $nombreRol, $titulo, $iniciales) {
      return [
          'id'       => $u->id,
          'nombre'   => $titulo($u->nombre),
          'username' => strtoupper((string) $u->username),
          'id_rol'   => (string) $u->id_rol,
          'rol'      => $nombreRol($u),
          'grupo'    => $grupoRol($u->id_rol),
          'activo'   => $u->activo === 'N' ? 'N' : 'S',
          'ini'      => $iniciales($titulo($u->nombre)),
          'rutas'    => $u->Detalles->pluck('RUTA')->map(function ($r) { return strtoupper(trim($r)); })->unique()->values(),
      ];
  })->values();
  $conteo = ['todos' => $datosUsuarios->count()];
  foreach (['admin', 'sac', 'sup', 'otros'] as $g) { $conteo[$g] = $datosUsuarios->where('grupo', $g)->count(); }
  $puedeAdministrar = in_array((string) Session::get('rol'), ['1', '2'], true);
@endphp

@section('metodosjs')
<script>
  window.SAC_USUARIOS = @json($datosUsuarios);
  window.SAC_RUTAS = @json($Rutas);
</script>
@include('jsViews.js_usuario')
@endsection

@section('content')
<main class="main" id="top">
  <div class="container-fluid" data-layout="container">
    <div class="content">
      @include('layouts.nav_importacion')

      <div class="sac-wrap">
        <div class="sac-head">
          <div>
            <h1 class="sac-h1">Usuarios</h1>
            <p class="sac-sub">{{ $conteo['todos'] }} usuarios · roles y rutas asignadas</p>
          </div>
          @if ($puedeAdministrar)
            <button type="button" class="btn btn-primary" id="sacUsrNuevo">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
              Nuevo usuario
            </button>
          @endif
        </div>

        <section class="card" style="overflow: hidden" aria-label="Lista de usuarios">
          <div class="sac-toolbar">
            <div class="nav-tabs m-0" role="group" aria-label="Filtrar por rol">
              <button type="button" class="nav-link active" data-usr-f="todos" aria-pressed="true">Todos <span class="sac-n">{{ $conteo['todos'] }}</span></button>
              <button type="button" class="nav-link" data-usr-f="admin" aria-pressed="false">Admin <span class="sac-n">{{ $conteo['admin'] }}</span></button>
              <button type="button" class="nav-link" data-usr-f="sac" aria-pressed="false">SAC <span class="sac-n">{{ $conteo['sac'] }}</span></button>
              <button type="button" class="nav-link" data-usr-f="sup" aria-pressed="false">Supervisores <span class="sac-n">{{ $conteo['sup'] }}</span></button>
              <button type="button" class="nav-link" data-usr-f="otros" aria-pressed="false">Otros <span class="sac-n">{{ $conteo['otros'] }}</span></button>
            </div>
            <div class="input-group ms-auto" style="max-width: 340px; flex: 0 1 340px">
              <span class="input-group-text"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg></span>
              <input class="form-control form-control-sm shadow-none" type="search" id="sacUsrBuscar" placeholder="Buscar por nombre, usuario o ruta…" aria-label="Buscar usuarios">
            </div>
          </div>

          <div class="sac-tblbox">
            <table class="table sac-usr-tbl mb-0" id="tbl_usuarios">
              <thead>
                <tr>
                  <th style="width: 56px"><span class="visually-hidden">Avatar</span></th>
                  <th>Usuario</th>
                  <th>Rol</th>
                  <th>Rutas asignadas</th>
                  <th class="text-end"><span class="visually-hidden">Acciones</span></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($datosUsuarios as $u)
                  <tr data-usr-id="{{ $u['id'] }}" data-grupo="{{ $u['grupo'] }}" @if ($u['activo'] !== 'S') class="sac-usr-off" title="Usuario inactivo" @endif data-buscar="{{ mb_strtolower($u['nombre'] . ' ' . $u['username'] . ' ' . $u['rol'] . ' ' . $u['rutas']->implode(' ')) }}">
                    <td><span class="sac-avatar">{{ $u['ini'] }}<i class="sac-avatar-dot {{ $u['activo'] === 'S' ? 'is-ok' : 'is-off' }}" aria-hidden="true"></i></span></td>
                    <td>
                      <span class="sac-cli-name d-block">{{ $u['nombre'] }}</span>
                      <span class="sac-cli-sub">{{ $u['username'] }}</span>
                    </td>
                    <td>
                      <div class="d-flex flex-column align-items-start gap-1">
                        <span class="sac-tag sac-rol-{{ $u['grupo'] }}">{{ $u['rol'] }}</span>
                      </div>
                    </td>
                    <td>
                      @if ($u['rutas']->count())
                        <div class="sac-chips">
                          @foreach ($u['rutas']->take(10) as $r)<span class="sac-chip-r">{{ $r }}</span>@endforeach
                          @if ($u['rutas']->count() > 10)<span class="sac-chip-r is-more">+{{ $u['rutas']->count() - 10 }} más</span>@endif
                        </div>
                      @else
                        <span class="sac-muted">Sin rutas asignadas</span>
                      @endif
                    </td>
                    <td class="text-end">
                      @if ($puedeAdministrar)
                        <div class="d-inline-flex gap-2">
                          <button type="button" class="btn btn-sm btn-falcon-default" data-usr-edit="{{ $u['id'] }}">Editar</button>
                          <button type="button" class="sac-ib sac-ib-danger" data-usr-del="{{ $u['id'] }}" aria-label="Eliminar a {{ $u['nombre'] }}" title="Eliminar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M10 11v6M14 11v6"></path></svg>
                          </button>
                        </div>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <p class="sac-empty" id="sacUsrVacio" hidden>No hay usuarios que coincidan con la búsqueda.</p>
          </div>
        </section>
      </div>
    </div>
  </div>
</main>

@if ($puedeAdministrar)
{{-- ========== MODAL: CREAR / EDITAR USUARIO ========== --}}
<div class="modal fade sac-umodal" id="sacUsrModal" tabindex="-1" aria-labelledby="sacUsrTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <form class="modal-content" id="sacUsrForm" novalidate>
      <div class="sac-mhead">
        <span class="sac-avatar sac-avatar-lg" id="sacUsrAv">+</span>
        <div class="sac-mhead-txt">
          <h2 class="sac-mtitle" id="sacUsrTitulo" style="font-size: 18px">Editar usuario</h2>
          <p class="sac-cli-sub mb-0" id="sacUsrSub"></p>
        </div>
        <button type="button" class="sac-ib sac-ib-ghost ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="modal-body sac-umodal-body">
       <div class="sac-ucol sac-pform">
        <p class="sac-sec">Datos del usuario</p>
        <div class="sac-pf-row3">
          <div class="sac-pf">
            <label class="form-label" for="sacUNombre">Nombre completo</label>
            <input class="form-control" id="sacUNombre" type="text" maxlength="255" required autocomplete="off">
          </div>
          <div class="sac-pf">
            <label class="form-label" for="sacUUser">Usuario</label>
            <input class="form-control" id="sacUUser" type="text" maxlength="60" required autocomplete="off" style="text-transform: uppercase">
          </div>
          <div class="sac-pf">
            <label class="form-label" for="sacURol">Rol</label>
            <select class="form-select" id="sacURol" required>
              <option value="">Selecciona…</option>
              @foreach ($Roles as $Rol)
                <option value="{{ $Rol->id }}">{{ $nombreRol((object) ['id_rol' => $Rol->id, 'RolName' => $Rol]) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="sac-pf">
          <span class="form-label">Estado</span>
          <label class="d-inline-flex align-items-center gap-2 m-0" style="cursor: pointer">
            <span class="sac-switch sac-switch-ok"><input type="checkbox" role="switch" id="sacUActivo" checked><span aria-hidden="true"></span></span>
            <span id="sacUActivoTxt">Activo · puede ingresar</span>
          </label>
        </div>

        <hr class="sac-hr">
        <p class="sac-sec">Acceso</p>
        <div class="sac-pf-row">
          <div class="sac-pf">
            <label class="form-label" for="sacUPass">Nueva contraseña</label>
            <input class="form-control" id="sacUPass" type="password" autocomplete="new-password" minlength="8">
          </div>
          <div class="sac-pf">
            <label class="form-label" for="sacUPass2">Confirmar contraseña</label>
            <input class="form-control" id="sacUPass2" type="password" autocomplete="new-password" placeholder="Repite la contraseña">
          </div>
        </div>
        <span class="sac-hint" id="sacUPassHint">Mínimo 8 caracteres.</span>

       </div>
       <div class="sac-ucol sac-ucol-rutas sac-pform">
        <p class="sac-sec">Rutas</p>
        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
          <div>
            <span class="sac-rt-title" id="sacURutasLbl">Rutas que puede trabajar</span>
            <span class="sac-cli-sub d-block" id="sacURutasCount">0 rutas seleccionadas</span>
          </div>
          <div class="sac-rt-links">
            <button type="button" class="sac-linkbtn" id="sacURutasTodas">Seleccionar todas</button>
            <span aria-hidden="true">·</span>
            <button type="button" class="sac-linkbtn" id="sacURutasNinguna">Quitar todas</button>
          </div>
        </div>
        <div class="sac-rt" id="sacURt">
          <div class="sac-rt-box" id="sacURtBox">
            <span id="sacURtChips" class="d-contents"></span>
            <input type="text" id="sacURtInput" placeholder="Agregar ruta…" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="sacURtList" aria-labelledby="sacURutasLbl">
            <button type="button" class="sac-rt-caret" id="sacURtCaret" aria-label="Mostrar rutas" tabindex="-1">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
            </button>
          </div>
          <div class="sac-rt-pop" id="sacURtPop" hidden>
            <div class="sac-rt-pophead"><span id="sacURtTotal"></span><span>Toca para agregar o quitar</span></div>
            <ul class="sac-rt-list" id="sacURtList" role="listbox" aria-multiselectable="true"></ul>
            <div class="sac-rt-popfoot"><span id="sacURtSel">0 seleccionadas</span><button type="button" class="btn btn-sm btn-falcon-default" id="sacURtListo">Listo</button></div>
          </div>
        </div>

       </div>
        <p class="sac-err mb-0 sac-uerr" id="sacUErr" role="alert" hidden></p>
      </div>

      <div class="modal-footer sac-umodal-foot">
        <button type="button" class="btn btn-falcon-danger me-auto" id="sacUEliminar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M10 11v6M14 11v6"></path></svg>
          Eliminar usuario
        </button>
        <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="sacUGuardar">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection
