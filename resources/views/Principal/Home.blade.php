@extends('layouts.lyt_gumadesk')
@section('sac_title', 'Inicio')
@section('metodosjs')
@include('jsViews.js_home')
<script>
  // Nueva UI: el menú lateral controla las pestañas (ocultas) de esta página vía #hash.
  (function () {
    var SECCIONES = {
      pedidos:     { tab: 'crm-pedido-tab',    titulo: 'Pedidos',      sub: 'Pedidos por rango de fechas, estado y SAC' },
      promociones: { tab: 'crm-promocion-tab', titulo: 'Promociones',  sub: 'Bonificaciones y descuentos que puedes ofrecer hoy' },
      inventario:  { tab: 'crm-revenue-tab',   titulo: 'Inventario',   sub: 'Existencias, precios y lotes por bodega' },
      clientes:    { tab: 'crm-profit-tab',    titulo: 'Clientes',     sub: 'Tu cartera con crédito, saldo y contacto' },
      liquidacion: { tab: 'crm-users-tab',     titulo: 'Liquidación',  sub: 'Lotes que vencen en los próximos 12 meses' },
      'liquidacion-6': { tab: 'crm-deals-tab', titulo: 'Liquidación',  sub: 'Lotes que vencen en los próximos 6 meses', nav: 'liquidacion' }
    };
    var TAB_A_SECCION = {};
    Object.keys(SECCIONES).forEach(function (k) { TAB_A_SECCION[SECCIONES[k].tab] = k; });

    function pintar(seccion) {
      var cfg = SECCIONES[seccion];
      if (!cfg) return;
      var nav = cfg.nav || seccion;
      document.getElementById('sacPageTitle').textContent = cfg.titulo;
      document.getElementById('sacPageSub').textContent = cfg.sub;
      var top = document.getElementById('sacTopTitle');
      if (top) top.textContent = cfg.titulo;
      document.title = cfg.titulo + ' · SAC Unimark';
      document.querySelectorAll('.sac-ni[data-sac-section]').forEach(function (a) {
        var on = a.getAttribute('data-sac-section') === nav;
        a.classList.toggle('on', on);
        if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
      });
      document.querySelectorAll('[data-sac-tab]').forEach(function (b) {
        var on = b.getAttribute('data-sac-tab') === cfg.tab;
        b.classList.toggle('active', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      document.querySelectorAll('[data-sac-only]').forEach(function (el) {
        var lista = el.getAttribute('data-sac-only').split(' ');
        el.classList.toggle('d-none', lista.indexOf(nav) === -1);
      });
    }

    function mostrar(seccion) {
      var cfg = SECCIONES[seccion];
      var link = cfg && document.getElementById(cfg.tab);
      if (!link) return false;
      bootstrap.Tab.getOrCreateInstance(link).show();
      pintar(seccion);
      return true;
    }

    document.querySelectorAll('#sacTabs [data-bs-toggle="tab"]').forEach(function (link) {
      link.addEventListener('shown.bs.tab', function () {
        if (window.jQuery && $.fn.dataTable) {
          $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
      });
    });

    document.querySelectorAll('[data-sac-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var seccion = TAB_A_SECCION[btn.getAttribute('data-sac-tab')];
        if (seccion) history.replaceState(null, '', '#' + seccion);
        mostrar(seccion);
      });
    });

    function desdeHash() {
      var h = (location.hash || '').replace('#', '');
      if (h && mostrar(h)) return;
      var activo = document.querySelector('#sacTabs .nav-link.active') || document.querySelector('#sacTabs .nav-link');
      if (activo) mostrar(TAB_A_SECCION[activo.id]);
    }
    window.addEventListener('hashchange', desdeHash);

    // Al iniciar (login, logo, recarga) siempre se abre Pedidos; el #hash solo se respeta
    // cuando se llega desde un enlace del menú lateral.
    var nav = (performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || {};
    var irA = null;
    try { irA = sessionStorage.getItem('sacGoto'); sessionStorage.removeItem('sacGoto'); } catch (e) {}
    if (irA && mostrar(irA)) {
      history.replaceState(null, '', '#' + irA);
    } else if (nav.type === 'reload' || !location.hash) {
      if (location.hash) history.replaceState(null, '', location.pathname + location.search);
      if (!mostrar('pedidos')) desdeHash();
    } else {
      desdeHash();
    }
  })();
</script>
<script>
  // ===== Nueva promoción (modal) =====
  (function () {
    var form = document.getElementById('sacPromoForm');
    if (!form) return;
    var $ = function (id) { return document.getElementById(id); };
    var img = $('sacPImg'), drop = $('sacPDrop'), prev = $('sacPPrev'), err = $('sacPErr');
    var PREV_VACIA = prev.innerHTML, MAX = 2 * 1024 * 1024;

    function error(msg) { err.textContent = msg || ''; err.hidden = !msg; }
    function limpiarImagen() {
      img.value = '';
      prev.innerHTML = PREV_VACIA;
      prev.classList.remove('has-img');
      $('sacPImgName').innerHTML = 'Arrastra una imagen o <span class="sac-drop-link">búscala</span>';
    }
    function usarArchivo(file) {
      error('');
      if (!file) return limpiarImagen();
      if (!/^image\/(png|jpe?g|webp)$/i.test(file.type)) { limpiarImagen(); return error('La imagen debe ser PNG, JPG o WEBP.'); }
      if (file.size > MAX) { limpiarImagen(); return error('La imagen pesa más de 2 MB.'); }
      var r = new FileReader();
      r.onload = function (e) {
        prev.innerHTML = '<img src="' + e.target.result + '" alt="Vista previa de la imagen">';
        prev.classList.add('has-img');
      };
      r.readAsDataURL(file);
      $('sacPImgName').textContent = file.name;
    }

    img.addEventListener('change', function () { usarArchivo(img.files[0]); });
    ['dragenter', 'dragover'].forEach(function (ev) {
      drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); });
    });
    drop.addEventListener('drop', function (e) {
      var f = e.dataTransfer && e.dataTransfer.files[0];
      if (!f) return;
      try { var dt = new DataTransfer(); dt.items.add(f); img.files = dt.files; } catch (x) {}
      usarArchivo(f);
    });

    // Fechas: dos calendarios simples (solo fecha) enlazados entre sí
    var ES = {
      weekdays: { shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'], longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] },
      months: { shorthand: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
                longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] },
      firstDayOfWeek: 1, ordinal: function () { return ''; }
    };
    function pick(el, onPick) {
      return window.flatpickr ? flatpickr(el, {
        dateFormat: 'Y-m-d', altInput: true, altFormat: 'j M Y', altInputClass: 'form-control sac-date-alt',
        locale: ES, static: true, disableMobile: true,
        onChange: function (d) { onPick(d[0] || null); if (d[0]) error(''); }
      }) : null;
    }
    // Artículo: select con búsqueda por código o nombre
    var selArt = $('sacPArticulo'), chArt = null;
    if (window.Choices) {
      var escA = function (v) { return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); };
      var cuerpo = function (d) {
        if (!d.value) return '<span class="sac-ch-ph">' + d.label + '</span>';
        var i = d.label.indexOf(' · ');
        var cod = i > -1 ? d.label.slice(0, i) : '', nom = i > -1 ? d.label.slice(i + 3) : d.label;
        return '<span class="sac-ch-cod">' + cod + '</span><span class="sac-ch-nom">' + nom + '</span>';
      };
      chArt = new Choices(selArt, {
        searchEnabled: true, shouldSort: false, searchResultLimit: 60, renderChoiceLimit: -1,
        searchFields: ['label', 'value'], position: 'bottom', itemSelectText: '',
        searchPlaceholderValue: 'Buscar por código o nombre…',
        noResultsText: 'No hay artículos que coincidan', noChoicesText: 'No hay artículos',
        fuseOptions: { threshold: 0.2, ignoreLocation: true },
        callbackOnCreateTemplates: function (template) {
          return {
            item: function (c, d) {
              return template('<div class="' + c.item + ' ' + (d.placeholder ? c.placeholder : c.itemSelectable) + '" data-item data-id="' + d.id + '" data-value="' + escA(d.value) + '"' + (d.active ? ' aria-selected="true"' : '') + '>' + cuerpo(d) + '</div>');
            },
            choice: function (c, d) {
              return template('<div class="' + c.item + ' ' + c.itemChoice + ' ' + (d.disabled ? c.itemDisabled : c.itemSelectable) + (d.placeholder ? ' ' + c.placeholder : '') + '" data-select-text="" data-choice ' + (d.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable') + ' data-id="' + d.id + '" data-value="' + escA(d.value) + '" role="option">' + cuerpo(d) + '</div>');
            }
          };
        }
      });
      selArt.addEventListener('change', function () { if (selArt.value) error(''); });
    }

    var fpIni, fpFin;
    fpIni = pick($('sacPIni'), function (d) {
      $('sacPInicioVal').value = d ? flatpickr.formatDate(d, 'Y-m-d') + ' 00:00' : '';
      if (fpFin) {
        fpFin.set('minDate', d || null);
        if (d && fpFin.selectedDates[0] && fpFin.selectedDates[0] < d) fpFin.clear();
        if (d && !fpFin.selectedDates[0]) fpFin.jumpToDate(d);
      }
    });
    fpFin = pick($('sacPFinD'), function (d) {
      $('sacPFinVal').value = d ? flatpickr.formatDate(d, 'Y-m-d') + ' 23:59' : '';
      if (fpIni) fpIni.set('maxDate', d || null);
    });

    form.addEventListener('submit', function (e) {
      error('');
      if (!$('sacPInicioVal').value || !$('sacPFinVal').value) {
        e.preventDefault();
        var falta = !$('sacPInicioVal').value ? fpIni : fpFin;
        if (falta) falta.open();
        return error('Elige la fecha de inicio y la de finalización de la promoción.');
      }
      if (!selArt.value) {
        e.preventDefault();
        if (chArt) chArt.showDropdown(); else selArt.focus();
        return error('Selecciona el artículo de la promoción.');
      }
      if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
      $('sacPGuardar').disabled = true;
      $('sacPGuardar').textContent = 'Guardando…';
      // Al volver del guardado, abrir la sección de Promociones
      try { sessionStorage.setItem('sacGoto', 'promociones'); } catch (x) {}
    });

    document.getElementById('sacPromoModal').addEventListener('hidden.bs.modal', function () {
      form.reset(); limpiarImagen(); error('');
      if (chArt) { chArt.removeActiveItems(); chArt.setChoiceByValue(''); }
      [fpIni, fpFin].forEach(function (fp) { if (fp) { fp.clear(); fp.set('minDate', null); fp.set('maxDate', null); } });
      $('sacPInicioVal').value = ''; $('sacPFinVal').value = '';
      $('sacPGuardar').disabled = false; $('sacPGuardar').textContent = 'Guardar';
    });
    document.getElementById('sacPromoModal').addEventListener('shown.bs.modal', function () { $('sacPTitulo').focus(); });
  })();
</script>
<script>
  // ===== Activar / desactivar promoción desde la tarjeta =====
  $(document).on('change', '[data-promo-toggle]', function () {
    var chk = this, id = chk.getAttribute('data-promo-toggle'), actual = chk.getAttribute('data-activo');
    var card = chk.closest('.sac-promo');
    chk.disabled = true;
    $.ajax({ url: 'editPromocion/' + id + '/' + actual, type: 'get', dataType: 'json' })
      .done(function () {
        var nuevo = actual === 'S' ? 'N' : 'S';
        chk.setAttribute('data-activo', nuevo);
        chk.checked = nuevo === 'S';
        card.classList.toggle('is-off', nuevo !== 'S');
        card.querySelector('.sac-promo-state').textContent = nuevo === 'S' ? 'Activa' : 'Inactiva';
      })
      .fail(function () {
        chk.checked = actual === 'S';
        if (window.Swal) Swal.fire('No se pudo actualizar', 'Intenta de nuevo en unos segundos.', 'error');
      })
      .always(function () { chk.disabled = false; });
  });
</script>
<script>
  // ===== Pedidos: dos calendarios (Desde / Hasta) y SAC =====
  (function () {
    var desde = document.getElementById('sacPedDesde'), hasta = document.getElementById('sacPedHasta'), oculto = document.getElementById('id_range_select');
    if (!desde || !hasta || !window.flatpickr) return;
    var M = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    var ES = {
      weekdays: { shorthand: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'], longhand: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] },
      months: { shorthand: M, longhand: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] },
      firstDayOfWeek: 1, ordinal: function () { return ''; }
    };
    // Valor inicial que deja js_home en el campo oculto: "AAAA-MM-DD to AAAA-MM-DD"
    var ini = (oculto.value || '').split('to').map(function (x) { return $.trim(x); }).filter(Boolean);
    var fpDesde, fpHasta;
    function consultar() {
      var a = fpDesde.selectedDates[0], b = fpHasta.selectedDates[0];
      if (!a || !b) return;
      var v = flatpickr.formatDate(a, 'Y-m-d') + ' to ' + flatpickr.formatDate(b, 'Y-m-d');
      if (v !== oculto.value) { oculto.value = v; $(oculto).trigger('change'); }
    }
    var base = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'j M Y', altInputClass: 'form-control', locale: ES, disableMobile: true };
    fpDesde = flatpickr(desde, $.extend({}, base, {
      defaultDate: ini[0] || null, maxDate: ini[1] || null,
      onChange: function (d) {
        if (fpHasta) {
          fpHasta.set('minDate', d[0] || null);
          // Si la fecha final quedó antes de la inicial, se iguala
          if (d[0] && fpHasta.selectedDates[0] && fpHasta.selectedDates[0] < d[0]) fpHasta.setDate(d[0], false);
        }
        consultar();
      }
    }));
    fpHasta = flatpickr(hasta, $.extend({}, base, {
      defaultDate: ini[1] || ini[0] || null, minDate: ini[0] || null, position: 'auto right',
      onChange: function (d) { fpDesde.set('maxDate', d[0] || null); consultar(); }
    }));
    $('#id_select_sac').on('change', function () { $(oculto).trigger('change'); });
  })();
</script>
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
              $rolHome  = (string) Session::get('rol');
              $esSACHome = in_array($rolHome, ['1', '2', '9']);
              $searchIco = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>';
              $filas = [5 => '5', 10 => '10', 20 => '20', -1 => 'Todas'];
            @endphp

            <div class="sac-wrap">

              <div class="sac-head">
                <div>
                  <h1 class="sac-h1" id="sacPageTitle">Inicio</h1>
                  <p class="sac-sub" id="sacPageSub">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div id="id_loading" class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Cargando…</span></div>
                  <button type="button" class="btn btn-falcon-default" id="sacPedExport" data-sac-only="pedidos"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="m7 10 5 5 5-5M12 15V3"></path></svg> Exportar</button>
                  <div class="sac-range2" data-sac-only="pedidos" role="group" aria-label="Rango de fechas de los pedidos">
                    <div class="sac-range">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
                      <label class="visually-hidden" for="sacPedDesde">Desde</label>
                      <input type="text" class="form-control" id="sacPedDesde" placeholder="Desde" readonly>
                    </div>
                    <span class="sac-range2-sep" aria-hidden="true">–</span>
                    <div class="sac-range">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
                      <label class="visually-hidden" for="sacPedHasta">Hasta</label>
                      <input type="text" class="form-control" id="sacPedHasta" placeholder="Hasta" readonly>
                    </div>
                    <input type="hidden" id="id_range_select">
                  </div>
                  @if (Session::get('rol') == '1' || Session::get('rol') == '2')
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sacPromoModal" data-sac-only="promociones">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
                    Nueva promoción
                  </button>
                  @endif
                  <div class="nav-tabs m-0" role="group" aria-label="Ventana de vencimiento" data-sac-only="liquidacion">
                    <button type="button" class="nav-link active" data-sac-tab="crm-users-tab" aria-pressed="true">12 meses <span class="sac-n" id="sac_liq_n12">0</span></button>
                    <button type="button" class="nav-link" data-sac-tab="crm-deals-tab" aria-pressed="false">6 meses <span class="sac-n" id="sac_liq_n6">0</span></button>
                  </div>
                  <button type="button" class="btn btn-falcon-default" id="sacLiqExport" data-sac-only="liquidacion">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="m7 10 5 5 5-5M12 15V3"></path></svg>
                    Exportar
                  </button>
                  <a class="btn btn-falcon-default" href="{{ route('generarExcel') }}" data-sac-only="inventario">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="m7 10 5 5 5-5M12 15V3"></path></svg>
                    Exportar Excel
                  </a>
                </div>
              </div>

              @if (Session::get('rol') != '10' || Session::get('rol') != '13')
              <input type="hidden" id="userId" value="{{ Session::get('rol') }}">
              <section class="card sac-kpis" aria-label="Mi progreso" data-sac-only="pedidos{{ $esSACHome ? '' : ' inventario' }}">
                <div class="sac-kpi">
                  <span class="sac-kl">Meta de venta</span>
                  <span class="sac-kv" id="id_Venta_Meta">…</span>
                  <span class="sac-muted">{{ ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('MMMM')) }}</span>
                </div>
                <div class="sac-kpi">
                  <span class="sac-kl">Venta real</span>
                  <span class="sac-kv" id="id_Venta_Real">…</span>
                  <span class="sac-muted sac-up">+<span id="id_Venta_Week">…</span> esta semana</span>
                  <span class="sac-muted">Hoy <span id="id_Venta_Actu">…</span></span>
                  <span class="d-none" id="id_Venta_Week_Label"></span>
                </div>
                <div class="sac-kpi">
                  <span class="sac-kl">Cumplimiento</span>
                  <span class="sac-kv" id="id_Venta_Porc">…</span>
                  <div class="sac-bar"><span id="sacBarVenta" style="width: 0%"></span></div>
                </div>
                <div class="sac-kpi">
                  <span class="sac-kl">Clientes atendidos</span>
                  <span class="sac-kv"><span id="id_Cliente_Real">…</span> <span class="sac-kv-of">/ <span id="id_Cliente_Meta">…</span></span></span>
                  <div class="sac-bar"><span id="sacBarClientes" style="width: 0%; background: var(--sac-teal)"></span></div>
                  <span class="d-none" id="id_Cliente_Porc"></span>
                </div>
                <div class="sac-kpi">
                  <span class="sac-kl">Mes transcurrido</span>
                  <span class="sac-kv" id="id_Dias_porcent">…</span>
                  <span class="sac-muted">Día <span id="id_Dias_Habiles">0</span> de <span id="id_Dias_Facturados">0</span></span>
                </div>
              </section>
              @endif

              <section class="card" style="overflow: hidden" aria-labelledby="sacPageTitle">

                {{-- Pestañas originales: se ocultan, la navegación ahora es el menú lateral (los IDs se conservan para el JS). --}}
                <ul class="nav nav-tabs d-none" id="sacTabs" role="tablist">
                  @if ($esSACHome)
                    <li class="nav-item" role="presentation"><a class="nav-link" id="crm-promocion-tab" data-bs-toggle="tab" href="#crm-promocion" role="tab" aria-controls="crm-promocion" aria-selected="false">Promoción</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link {{$SAC}}" id="crm-pedido-tab" data-bs-toggle="tab" href="#crm-pedido" role="tab" aria-controls="crm-pedido" aria-selected="false">Pedidos</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" id="crm-revenue-tab" data-bs-toggle="tab" href="#crm-revenue" role="tab" aria-controls="crm-revenue" aria-selected="true">Inventario</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" id="crm-profit-tab" data-bs-toggle="tab" href="#crm-profit" role="tab" aria-controls="crm-profit" aria-selected="false">Clientes</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" id="crm-users-tab" data-bs-toggle="tab" href="#crm-users" role="tab" aria-controls="crm-users" aria-selected="false">Liquidación 12 meses</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" id="crm-deals-tab" data-bs-toggle="tab" href="#crm-deals" role="tab" aria-controls="crm-deals" aria-selected="false">Liquidación 6 meses</a></li>
                  @elseif ($rolHome == '10')
                    <li class="nav-item" role="presentation"><a class="nav-link {{$Normal}}" id="crm-revenue-tab" data-bs-toggle="tab" href="#crm-revenue" role="tab" aria-controls="crm-revenue" aria-selected="true">Inventario</a></li>
                  @endif
                </ul>

                <div class="tab-content">

                  {{-- ========== INVENTARIO ========== --}}
                  <div class="tab-pane {{$Normal}}" id="crm-revenue" role="tabpanel" aria-labelledby="crm-revenue-tab">
                    <div class="sac-toolbar">
                      <div class="d-flex flex-wrap gap-2" role="group" aria-label="Filtrar artículos">
                        <button type="button" class="sac-chip on" data-inv-chip="todos" aria-pressed="true">Todos</button>
                        <button type="button" class="sac-chip" data-inv-chip="bonif" aria-pressed="false">Con bonificación</button>
                        <button type="button" class="sac-chip" data-inv-chip="revision" aria-pressed="false">En revisión</button>
                      </div>
                      <div class="input-group ms-auto" style="max-width: 320px; flex: 0 1 320px">
                        <span class="input-group-text">{!! $searchIco !!}</span>
                        <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar artículo o código…" aria-label="Buscar en inventario" id="tbl_buscar_inventario" />
                      </div>
                      <label class="d-flex align-items-center gap-2 sac-muted">
                        <select class="form-select form-select-sm" style="width: auto" id="tbl_select_inventario">
                          @foreach ([10 => '10', 20 => '20', 50 => '50', -1 => 'Todas'] as $v => $t)<option value="{{ $v }}" @if($v == 10) selected @endif>{{ $t }}</option>@endforeach
                        </select>
                      </label>
                    </div>
                    <div class="sac-tblbox"><table id="tbl_inventario" class="table overflow-hidden" style="width:100%"></table></div>
                  </div>

                  {{-- ========== CLIENTES ========== --}}
                  <div class="tab-pane" id="crm-profit" role="tabpanel" aria-labelledby="crm-profit-tab">
                    <div class="sac-toolbar">
                      <div class="nav-tabs m-0" role="group" aria-label="Filtrar clientes">
                        <button type="button" class="nav-link active" data-cli-f="todos" aria-pressed="true">Todos <span class="sac-n" id="sac_cli_n_todos">0</span></button>
                        <button type="button" class="nav-link" data-cli-f="moroso" aria-pressed="false">Morosos <span class="sac-n" id="sac_cli_n_moroso">0</span></button>
                        <button type="button" class="nav-link" data-cli-f="saldo" aria-pressed="false">Con saldo <span class="sac-n" id="sac_cli_n_saldo">0</span></button>
                      </div>
                      <div class="input-group ms-auto" style="max-width: 320px; flex: 0 1 320px">
                        <span class="input-group-text">{!! $searchIco !!}</span>
                        <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar cliente, código o dirección…" aria-label="Buscar clientes" id="tbl_buscar_cliente" />
                      </div>
                      <label class="d-flex align-items-center gap-2 sac-muted">Mostrar
                        <select class="form-select form-select-sm" style="width: auto" id="tbl_select_cliente">
                          @foreach ([10 => '10', 25 => '25', 50 => '50', -1 => 'Todos'] as $v => $t)<option value="{{ $v }}" @if($v == 25) selected @endif>{{ $t }}</option>@endforeach
                        </select>
                      </label>
                    </div>
                    <div class="sac-tblbox"><table id="tbl_mst_clientes" class="table sac-cli-tbl overflow-hidden" style="width:100%"></table></div>
                    <div class="sac-tfoot"><span>Mostrando <span id="sac_cli_shown">0</span> de <span id="sac_cli_total">0</span> clientes</span><span>Toca un cliente para ver su ficha</span></div>
                  </div>

                  {{-- ========== PROMOCIONES ========== --}}
                  <div class="tab-pane" id="crm-promocion" role="tabpanel" aria-labelledby="crm-promocion-tab">
                    <div style="padding: 16px">
                      @if (count($promocion))
                        @php
                          $hoy = \Carbon\Carbon::today();
                          $mesesCortos = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
                          $fmtCorto = function ($d) use ($mesesCortos, $hoy) {
                              return $d->day . ' ' . $mesesCortos[$d->month - 1] . ($d->year != $hoy->year ? ' ' . $d->year : '');
                          };
                          $puedeEditar = in_array((string) Session::get('rol'), ['1', '2']);
                          $tonos = ['blue', 'cyan', 'amber', 'green'];
                        @endphp
                        <div class="sac-promos">
                          @foreach($promocion as $promo)
                            @php
                              $ini   = \Carbon\Carbon::parse($promo->fechaInicio)->startOfDay();
                              $fin   = \Carbon\Carbon::parse($promo->fechaFinal)->startOfDay();
                              $total = max(1, $ini->diffInDays($fin) + 1);
                              if ($hoy->lt($ini)) {
                                  $dias = $hoy->diffInDays($ini); $avance = 0;
                                  $estado = $dias == 1 ? 'Inicia mañana' : 'Inicia en ' . $dias . ' días';
                              } elseif ($hoy->gt($fin)) {
                                  $avance = 100; $estado = 'Finalizada';
                              } else {
                                  $dias = $hoy->diffInDays($fin); $avance = min(100, round(($ini->diffInDays($hoy) + 1) / $total * 100));
                                  $estado = $dias == 0 ? 'Último día' : ($dias == 1 ? 'Queda 1 día' : 'Quedan ' . $dias . ' días');
                              }
                              $art    = $artPromo[$promo->articulo] ?? null;
                              $lab    = $art ? trim($art->LABORATORIO) : '';
                              $unidad = $art ? trim($art->UNIDAD_ALMACEN) : '';
                              $reglas = ($art && $art->REGLAS && $art->REGLAS != '0') ? array_filter(array_map('trim', explode(',', $art->REGLAS))) : [];
                              $oferta = count($reglas) ? preg_replace('/\s*([+x×])\s*/u', ' $1 ', reset($reglas)) : null;
                              $tieneImg = $promo->image && $promo->image != 'item.jpg';
                              $tono   = $tonos[$loop->index % count($tonos)];
                              $activa = strtoupper((string) $promo->activo) == 'S';
                            @endphp
                            <article class="sac-promo sac-tono-{{ $tono }} {{ $activa ? '' : 'is-off' }}" data-promo="{{ $promo->id }}">
                              @php $conBanner = $tieneImg && $promo->image_url; @endphp
                              <div class="sac-promo-band {{ $conBanner ? 'has-banner' : '' }}">
                                @if ($conBanner)
                                  <img class="sac-promo-banner" src="{{ $promo->image_url }}" alt="" loading="lazy" onerror="this.closest('.sac-promo-band').classList.remove('has-banner'); this.remove();">
                                @endif
                                <div class="sac-promo-band-top">
                                  <span class="sac-promo-lab">{{ $lab !== '' ? (stripos($lab, 'LAB') === 0 ? $lab : 'LAB. ' . $lab) : 'PROMOCIÓN' }}</span>
                                  <svg class="sac-promo-ico" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5"></circle></svg>
                                </div>
                                <p class="sac-promo-offer {{ $oferta ? '' : 'is-fallback' }}">{{ $oferta ?: 'Promoción' }}</p>
                              </div>

                              <div class="sac-promo-body">
                                <h3 class="sac-promo-t">
                                  <button type="button" class="sac-promo-link" onclick="promocionLeerMas('{{ $promo->id }}')" title="Ver condiciones de la promoción">{{ $promo->titulo }}</button>
                                </h3>
                                <p class="sac-promo-n">{{ $promo->nombre }}{{ $unidad !== '' ? ' · ' . $unidad : '' }}</p>

                                <div class="sac-promo-dates">
                                  <span class="sac-num">{{ $fmtCorto($ini) }} – {{ $fmtCorto($fin) }}</span>
                                  <strong>{{ $estado }}</strong>
                                </div>
                                <div class="sac-promo-bar" role="progressbar" aria-label="Avance de la vigencia" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $avance }}"><span style="width: {{ $avance }}%"></span></div>
                              </div>

                              <div class="sac-promo-foot">
                                <span class="sac-promo-state">{{ $activa ? 'Activa' : 'Inactiva' }}</span>
                                @if ($puedeEditar)
                                  <label class="sac-switch" title="Activar o desactivar">
                                    <input type="checkbox" role="switch" {{ $activa ? 'checked' : '' }} data-promo-toggle="{{ $promo->id }}" data-activo="{{ $activa ? 'S' : 'N' }}" aria-label="Promoción {{ $promo->titulo }} activa">
                                    <span aria-hidden="true"></span>
                                  </label>
                                @endif
                              </div>
                            </article>
                          @endforeach
                        </div>
                      @else
                        <p class="sac-empty">No hay promociones activas.</p>
                      @endif
                    </div>
                  </div>

                  {{-- ========== PEDIDOS ========== --}}
                  <div class="tab-pane {{$SAC}}" id="crm-pedido" role="tabpanel" aria-labelledby="crm-pedido-tab">
                    <div class="sac-toolbar">
                      <div class="nav-tabs m-0" role="group" aria-label="Filtrar por estado">
                        <button type="button" class="nav-link active" data-ped-f="pend" aria-pressed="true">Pendientes <span class="sac-n" id="sac_ped_n_pend">0</span></button>
                        <button type="button" class="nav-link" data-ped-f="todos" aria-pressed="false">Todos <span class="sac-n" id="sac_ped_n_todos">0</span></button>
                        <button type="button" class="nav-link" data-ped-f="proc" aria-pressed="false">Procesados <span class="sac-n" id="sac_ped_n_proc">0</span></button>
                        <button type="button" class="nav-link" data-ped-f="canc" aria-pressed="false">Cancelados <span class="sac-n" id="sac_ped_n_canc">0</span></button>
                      </div>
                      <label class="d-flex align-items-center gap-2 sac-muted"><span class="visually-hidden">Ruta</span>
                        <select class="form-select form-select-sm" style="width: auto" id="sacPedRuta">
                          <option value="">Todas las rutas</option>
                        </select>
                      </label>
                      @if( Session::get('rol') == '1' || Session::get('rol') == '2')
                        <label class="d-flex align-items-center gap-2 sac-muted"><span class="visually-hidden">SAC</span>
                          <select class="form-select form-select-sm" style="width: auto" id="id_select_sac">
                            <option value="0">Todos los SAC</option>
                            @foreach ($Lista_SAC as $vnd)
                              <option value="{{$vnd->username}}">{{$vnd->nombre}}</option>
                            @endforeach
                          </select>
                        </label>
                      @endif
                      <select class="d-none" id="id_select_status" aria-hidden="true" tabindex="-1">
                        <option value="-1" selected>Todos</option>
                        <option value="0">Pendientes</option>
                        <option value="1">Procesado</option>
                        <option value="2">Cancelado</option>
                      </select>
                      <div class="input-group ms-auto" style="max-width: 240px; flex: 1 1 120px; min-width: 120px">
                        <span class="input-group-text">{!! $searchIco !!}</span>
                        <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar…" aria-label="Buscar pedido o cliente" id="id_txt_buscar" />
                      </div>
                      <div class="d-flex gap-2 flex-nowrap">
                      <button type="button" class="btn btn-sm btn-falcon-default" id="sacPedTodo" data-abrir="1">Expandir todo</button>
                      <button type="button" class="btn btn-sm sac-refresh" id="sacPedActualizar" title="Consulta solo los pedidos pendientes, sin recargar la página">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M3 21v-5h5"></path><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                        <span>Actualizar</span>
                      </button>
                      <span class="visually-hidden" id="sacPedActualizarEstado" role="status" aria-live="polite"></span>
                      </div>
                    </div>
                    <div class="sac-tblbox"><table id="tbl_mst_pedido" class="table sac-ped-tbl overflow-hidden" style="width:100%"></table></div>
                    <div class="sac-tfoot sac-liq-foot" id="sacPedFoot"><span id="sac_ped_info"></span></div>
                  </div>

                  {{-- ========== LIQUIDACIÓN 12 MESES ========== --}}
                  <div class="tab-pane" id="crm-users" role="tabpanel" aria-labelledby="crm-users-tab">
                    <div class="sac-toolbar">
                      
                      <div class="sac-legend" aria-label="Leyenda de días restantes">
                        <span><i class="is-bad"></i>Menos de 90 días</span>
                        <span><i class="is-warn"></i>90 a 180 días</span>
                        <span><i class="is-info"></i>Más de 180 días</span>
                      </div>
                      <div class="input-group ms-auto" style="max-width: 340px; flex: 0 1 340px">
                        <span class="input-group-text">{!! $searchIco !!}</span>
                        <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar por artículo, código, laboratorio o lote…" aria-label="Buscar en liquidación 12 meses" id="tbl_buscar_liq12" />
                      </div>
                      <label class="d-flex align-items-center gap-2 sac-muted">
                        <select class="form-select form-select-sm" style="width: auto" id="tbl_select_liq12">
                          @foreach ($filas as $v => $t)<option value="{{ $v }}" @if($v == 5) selected @endif>{{ $t }}</option>@endforeach
                        </select>
                      </label>
                    </div>
                    <div class="sac-tblbox"><table id="tbl_inventario_liq_12" class="table sac-liq-tbl overflow-hidden" style="width:100%"></table></div>
                    <div class="sac-tfoot sac-liq-foot" data-liq-foot="tbl_inventario_liq_12"><span class="sac-liq-info"></span></div>
                  </div>

                  {{-- ========== LIQUIDACIÓN 6 MESES ========== --}}
                  <div class="tab-pane" id="crm-deals" role="tabpanel" aria-labelledby="crm-deals-tab">
                    <div class="sac-toolbar">
                      
                      <div class="sac-legend" aria-label="Leyenda de días restantes">
                        <span><i class="is-bad"></i>Menos de 90 días</span>
                        <span><i class="is-warn"></i>90 a 180 días</span>
                        <span><i class="is-info"></i>Más de 180 días</span>
                      </div>
                      <div class="input-group ms-auto" style="max-width: 340px; flex: 0 1 340px">
                        <span class="input-group-text">{!! $searchIco !!}</span>
                        <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar por artículo, código, laboratorio o lote…" aria-label="Buscar en liquidación 6 meses" id="tbl_buscar_liq6" />
                      </div>
                      <label class="d-flex align-items-center gap-2 sac-muted">
                        <select class="form-select form-select-sm" style="width: auto" id="tbl_select_liq6">
                          @foreach ($filas as $v => $t)<option value="{{ $v }}" @if($v == 5) selected @endif>{{ $t }}</option>@endforeach
                        </select>
                      </label>
                    </div>
                    <div class="sac-tblbox"><table id="tbl_inventario_liq_6" class="table sac-liq-tbl overflow-hidden" style="width:100%"></table></div>
                    <div class="sac-tfoot sac-liq-foot" data-liq-foot="tbl_inventario_liq_6"><span class="sac-liq-info"></span></div>
                  </div>

                </div>
              </section>
            </div>
        <!--OPEN MODALS -->
    
       
        <div class="modal fade sac-imodal" id="modal_info_inventario" tabindex="-1" aria-labelledby="id_nombre_articulo" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">

              <div class="sac-mhead">
                <span class="sac-thumb sac-thumb-lg" id="sacInvHeadThumb" aria-hidden="true"></span>
                <div class="sac-mhead-txt">
                  <h2 class="sac-mtitle sac-imodal-title"><span id="id_nombre_articulo">Cargando…</span><span class="sac-imodal-unit" id="sacInvUnidad"></span></h2>
                  <p class="sac-cli-sub mb-0"><span id="id_codigo_articulo"></span><span class="d-none" id="lbl_unidad"></span></p>
                </div>
                <div class="spinner-border spinner-border-sm text-primary ms-auto" role="status" id="id_load_articulo"><span class="visually-hidden">Cargando…</span></div>
                <button type="button" class="sac-ib sac-ib-ghost" data-bs-dismiss="modal" aria-label="Cerrar">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
              </div>

              <div class="modal-body sac-imodal-body">
                <div class="sac-imodal-top">
                  {{-- Imagen del producto (al tocarla: ver en grande o, si no tiene, subir una) --}}
                  <div class="sac-imodal-img" id="artImg">
                    <span class="sac-imodal-ph">
                      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>
                      Imagen del producto
                    </span>
                  </div>

                  <div class="sac-imodal-info">
                    <div class="sac-prices">
                      <div class="sac-price"><span class="sac-kl">Farmacia</span><strong class="sac-num" id="id_precio_farmacia">C$ —</strong></div>
                      <div class="sac-price"><span class="sac-kl">Mayorista</span><strong class="sac-num" id="id_precio_mayorista">C$ —</strong></div>
                      @if (Session::get('rol') == '1' || Session::get('rol') == '2' || Session::get('rol') == '9')
                        <div class="sac-price"><span class="sac-kl">Inst. pública</span><strong class="sac-num" id="id_precio_inst_pub">C$ —</strong></div>
                      @endif
                      <div class="sac-price"><span class="sac-kl">Público</span><strong class="sac-num" id="id_precio_mayortista">C$ —</strong></div>
                    </div>
                    <div class="sac-imodal-meta">
                      <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="sac-muted">Bonificación</span>
                        <span id="id_reglas" class="d-inline-flex flex-wrap gap-1"></span>
                      </div>
                      @if (Session::get('rol') == '1' || Session::get('rol') == '2')
                        <label class="d-inline-flex align-items-center gap-2 m-0" style="cursor: pointer">
                          <span class="sac-switch sac-switch-ok"><input type="checkbox" role="switch" id="opcion1" name="opciones[]" value=""><span aria-hidden="true"></span></span>
                          <span class="sac-check-txt">En revisión</span>
                        </label>
                      @else
                        <span class="sac-tag sac-tag-warn d-none" id="sacInvRevision">En revisión</span>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="sac-imodal-stock">
                  <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <h3 class="sac-card-title">Existencias por bodega</h3>
                    <span class="sac-muted">Total disponible <strong class="sac-num" id="sacInvTotal">—</strong></span>
                  </div>
                  <div class="sac-bods" id="sacInvBodegas" aria-live="polite"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade sac-modal" id="modal_info_cliente" data-bs-keyboard="true" data-bs-backdrop="static" tabindex="-1" aria-labelledby="lbl_nombre_cliente" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">

              <div class="sac-mhead">
                <span class="sac-avatar sac-avatar-lg"><span id="sac_cli_avatar_txt">…</span><i class="sac-avatar-dot is-ok" id="sac_cli_avatar_dot" aria-hidden="true"></i></span>
                <div class="sac-mhead-txt">
                  <h2 class="sac-mtitle" id="lbl_nombre_cliente">Cargando…</h2>
                  <p class="sac-cli-sub mb-0"><span id="lbl_codigo"></span><span id="sac_cli_ruc"></span> · Ruta <span id="lbl_rutas"></span> · Última factura <span id="lbl_last_sale"></span></p>
                </div>
                <div class="spinner-border spinner-border-sm text-primary ms-auto" role="status" id="id_load_cliente"><span class="visually-hidden">Cargando…</span></div>
                <button type="button" class="sac-ib sac-mclose" data-bs-dismiss="modal" aria-label="Cerrar">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
              </div>

              <div class="modal-body sac-mbody">
                <div class="sac-mgrid">

                  {{-- Columna principal --}}
                  <div class="sac-mmain">
                    <div class="card sac-kpis sac-kpis-4">
                      <div class="sac-kpi"><span class="sac-kl">Límite de crédito</span><span class="sac-kv" id="lbl_limite">C$ 0.00</span></div>
                      <div class="sac-kpi"><span class="sac-kl">Saldo</span><span class="sac-kv" id="lbl_saldo">C$ 0.00</span></div>
                      <div class="sac-kpi"><span class="sac-kl">Disponible</span><span class="sac-kv sac-amt-ok" id="lbl_disponible">C$ 0.00</span></div>
                      <div class="sac-kpi"><span class="sac-kl">No. acciones</span><span class="sac-kv" id="lbl_acciones">0</span></div>
                    </div>

                    <div class="card" style="overflow: hidden">
                      <ul class="nav nav-tabs sac-tabs-line" id="sacCliTabs" role="tablist" data-tab-has-echarts="data-tab-has-echarts">
                        <li class="nav-item" role="presentation"><a class="nav-link active" id="historico-factura-tab" data-bs-toggle="tab" href="#historico-factura" role="tab" aria-controls="historico-factura" aria-selected="true">Histórico de facturas</a></li>
                        <li class="nav-item" role="presentation"><a class="nav-link" id="Ultm-3meses-tab" data-bs-toggle="tab" href="#Ultm-3meses" role="tab" aria-controls="Ultm-3meses" aria-selected="false">Últimos 3 meses</a></li>
                        <li class="nav-item" role="presentation"><a class="nav-link" id="art-no-fact-tab" data-bs-toggle="tab" href="#art-no-fact" role="tab" aria-controls="art-no-fact" aria-selected="false">Artículos no facturados</a></li>
                        @if (Session::get('rol') == '1')
                          <li class="nav-item" role="presentation"><a class="nav-link" id="plan-creci-tab" data-bs-toggle="tab" href="#plan-creci" role="tab" aria-controls="plan-creci" aria-selected="false">Plan de crecimiento</a></li>
                        @endif
                      </ul>

                      <div class="tab-content">
                        <div class="tab-pane active" id="historico-factura" role="tabpanel" aria-labelledby="historico-factura-tab">
                          <div class="sac-toolbar">
                            <div class="input-group" style="max-width: 320px; flex: 1 1 240px">
                              <span class="input-group-text">{!! $searchIco !!}</span>
                              <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar factura…" aria-label="Buscar factura" id="tbl_buscar_history_factura" />
                            </div>
                            <span class="sac-muted ms-auto" id="sac_fact_count"></span>
                          </div>
                          <div class="sac-tblbox"><table id="tbl_historico_factura" class="table sac-list overflow-hidden" style="width:100%"></table></div>
                        </div>

                        <div class="tab-pane" id="Ultm-3meses" role="tabpanel" aria-labelledby="Ultm-3meses-tab">
                          <div class="sac-toolbar">
                            <div class="input-group" style="max-width: 320px; flex: 1 1 240px">
                              <span class="input-group-text">{!! $searchIco !!}</span>
                              <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar artículo…" aria-label="Buscar en últimos 3 meses" id="tbl_buscar_last3m" />
                            </div>
                          </div>
                          <div class="sac-tblbox"><table id="tbl_ultm_3m" class="table sac-list overflow-hidden" style="width:100%"></table></div>
                        </div>

                        <div class="tab-pane" id="art-no-fact" role="tabpanel" aria-labelledby="art-no-fact-tab">
                          <div class="sac-toolbar">
                            <div class="input-group" style="max-width: 320px; flex: 1 1 240px">
                              <span class="input-group-text">{!! $searchIco !!}</span>
                              <input class="form-control form-control-sm shadow-none search" type="search" placeholder="Buscar artículo…" aria-label="Buscar artículos no facturados" id="tbl_search_nofacturado" />
                            </div>
                          </div>
                          <div class="sac-tblbox"><table id="tbl_no_facturado" class="table sac-list overflow-hidden" style="width:100%"></table></div>
                        </div>

                        <div class="tab-pane" id="plan-creci" role="tabpanel" aria-labelledby="plan-creci-tab">
                          <div class="sac-toolbar">
                            <div class="form-check form-switch mb-0">
                              <input class="form-check-input" id="flexSwitchCheckDefault" type="checkbox" oninput="AddPlan()" />
                              <label class="form-check-label" for="flexSwitchCheckDefault">Plan de crecimiento</label>
                            </div>
                            <strong class="ms-auto sac-num" id="id_ttmes">C$ 0.00</strong>
                          </div>
                          <div class="sac-kpis sac-kpis-4" style="border-bottom: 1px solid var(--sac-border-soft)">
                            <div class="sac-kpi"><span class="sac-kl">Monto base</span><span class="sac-kv" id="id_monto_base">C$ 0.00</span></div>
                            <div class="sac-kpi"><span class="sac-kl">Crecimiento (25 %)</span><span class="sac-kv" id="id_crecimiento_esperado">C$ 0.00</span></div>
                            <div class="sac-kpi"><span class="sac-kl">Compra mínima mes</span><span class="sac-kv" id="id_crecimiento_minimo">C$ 0.00</span></div>
                            <div class="sac-kpi"><span class="sac-kl">% crecimiento</span><span class="sac-kv" id="id_porcent_crecimientio">0 %</span></div>
                          </div>
                          <div class="echart-sale-VentasDelMes" data-echart-responsive="true" data-echart-tab="data-echart-tab" style="height: 320px; padding: 8px 12px"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Columna lateral --}}
                  <aside class="sac-mside">
                    <section class="card sac-side-card" aria-labelledby="sacMoraTitle">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="sac-card-title" id="sacMoraTitle">Saldos en mora</h3>
                        <span class="sac-muted sac-num" id="sac_mora_total">C$ 0.00</span>
                      </div>
                      <div class="sac-mora-bar" id="sac_mora_bar" aria-hidden="true"></div>
                      <ul class="sac-mora">
                        <li><span><i style="background: #93C5FD"></i>No vencido</span><span class="sac-num">C$ <span id="no_fact">0.00</span></span></li>
                        <li><span><i style="background: #FACC15"></i>30 días</span><span class="sac-num">C$ <span id="30_dias">0.00</span></span></li>
                        <li><span><i style="background: #F59E0B"></i>60 días</span><span class="sac-num">C$ <span id="60_dias">0.00</span></span></li>
                        <li><span><i style="background: #EA580C"></i>90 días</span><span class="sac-num">C$ <span id="90_dias">0.00</span></span></li>
                        <li><span><i style="background: #C2410C"></i>120 días</span><span class="sac-num">C$ <span id="120_dias">0.00</span></span></li>
                        <li><span><i style="background: #7F1D1D"></i>Más de 120 días</span><span class="sac-num">C$ <span id="mas_120_dias">0.00</span></span></li>
                      </ul>
                    </section>

                    <section class="card sac-side-card" aria-labelledby="sacContactoTitle">
                      <h3 class="sac-card-title mb-2" id="sacContactoTitle">Contacto</h3>
                      <dl class="sac-dl">
                        <dt>Teléfonos</dt><dd class="sac-num" id="sac_cli_tels">—</dd>
                        <dt>Dirección</dt><dd id="sac_cli_dir">—</dd>
                      </dl>
                    </section>
                  </aside>

                </div>
              </div>
            </div>
          </div>
        </div>

        @if (Session::get('rol') == '1' || Session::get('rol') == '2')
        {{-- ========== MODAL: CREAR PROMOCIÓN (reemplaza al del calendario) ========== --}}
        <div class="modal fade sac-pmodal" id="sacPromoModal" tabindex="-1" aria-labelledby="sacPromoTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" id="sacPromoForm" action="{{ route('insert_promocion') }}" method="POST" enctype="multipart/form-data" novalidate>
              @csrf
              <div class="sac-mhead">
                <h2 class="sac-mtitle" id="sacPromoTitle" style="font-size: 18px">Crear promoción</h2>
                <button type="button" class="sac-ib sac-ib-ghost ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
              </div>

              <div class="modal-body sac-pform">
                <div class="sac-pf">
                  <label class="form-label" for="sacPTitulo">Título</label>
                  <input class="form-control" id="sacPTitulo" type="text" name="title" maxlength="150" required placeholder="Ej. Bonificación analgésicos noviembre">
                </div>

                <div class="sac-pf-row">
                  <div class="sac-pf">
                    <label class="form-label" for="sacPIni">Fecha de inicio</label>
                    <div class="sac-date">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
                      <input class="form-control" id="sacPIni" type="text" placeholder="Elige una fecha" autocomplete="off" required>
                    </div>
                  </div>
                  <div class="sac-pf">
                    <label class="form-label" for="sacPFinD">Fecha de finalización</label>
                    <div class="sac-date">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
                      <input class="form-control" id="sacPFinD" type="text" placeholder="Elige una fecha" autocomplete="off" required>
                    </div>
                  </div>
                  <input type="hidden" name="startDate" id="sacPInicioVal">
                  <input type="hidden" name="endDate" id="sacPFinVal">
                </div>

                <div class="sac-pf">
                  <label class="form-label" for="sacPDesc">Descripción</label>
                  <textarea class="form-control" id="sacPDesc" name="description" rows="3" required placeholder="Regla de la promoción, condiciones, clientes a los que aplica…"></textarea>
                </div>

                <div class="sac-pf">
                  <label class="form-label" for="sacPArticulo">Artículo</label>
                  <select class="form-select" id="sacPArticulo" name="label" required>
                    <option value="" selected placeholder>Seleccione un artículo</option>
                    @foreach ($articulos as $art)
                      <option value="{{ $art->ARTICULO }}!{{ strtoupper($art->DESCRIPCION) }}">{{ $art->ARTICULO }} · {{ strtoupper($art->DESCRIPCION) }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="sac-pf">
                  <span class="form-label d-block" id="sacPImgLbl">Imagen</span>
                  <div class="sac-drop-row">
                    <label class="sac-drop" id="sacPDrop" for="sacPImg" aria-describedby="sacPImgHint">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="m17 8-5-5-5 5M12 3v12"></path></svg>
                      <span class="sac-drop-t" id="sacPImgName">Arrastra una imagen o <span class="sac-drop-link">búscala</span></span>
                      <span class="sac-drop-h" id="sacPImgHint">PNG, JPG o WEBP · máximo 2 MB</span>
                      <input class="visually-hidden" type="file" id="sacPImg" name="nuevaImagen" accept="image/png,image/jpeg,image/webp" aria-labelledby="sacPImgLbl">
                    </label>
                    <div class="sac-prev" id="sacPPrev" aria-live="polite">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><path d="m3.3 7 8.7 5 8.7-5"></path></svg>
                      <span>Vista previa</span>
                    </div>
                  </div>
                </div>

                <p class="sac-err mb-0" id="sacPErr" role="alert" hidden></p>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="sacPGuardar">Guardar</button>
              </div>
            </form>
          </div>
        </div>
        @endif

        <div class="modal fade" id="IdmdlComment" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">

            <div class="position-absolute top-0 end-0 mt-3 me-3 z-index-1">
                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="card-header bg-light">
                <div class="row justify-content-between">
                  <div class="col">
                    <div class="d-flex">
                      <div class="avatar avatar-2xl">
                        <img class="rounded-circle" src="{{ asset('images/item.png') }}" alt="" />

                      </div>
                      <div class="flex-1 align-self-center ms-2">
                        <p class="mb-1 lh-1"><a class="fw-semi-bold" href="!#" id="id_modal_name_item" >Nombre Item</a></p>
                        <p class="mb-0 fs--1"><span id="id_modal_articulo"></span> </p>
                        <p class="mb-0 fs--1">#<span id="id_modal_nSoli"></span> &bull; <span id="id_modal_Fecha"></span> <span class="fas fa-calendar"></span></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="modal-body">
                <div class="mb-3">
                    <h6 class="fs-0 mb-0"> <span id="id_comentario_pedido"></span></h6>
                </div>
                <div class="d-flex align-items-center border-top border-200 pt-3">

                
                  <div class="avatar avatar-xl">
                    <img class="rounded-circle" src="{{ asset('images/user/avatar-4.jpg') }}" alt="" />
                  </div>
                  <input class="form-control rounded-pill ms-2 fs--1" type="text" placeholder="Escribe un Comentario..." id="id_textarea_comment" />
                </div>
        

                <div class="d-flex mt-3">
                 
                
                <div id="id_comment_item"></div>
                
              </div>

            </div>
            
          </div>
        </div>
        
        <!--CLOSE MODALS -->
    </div>
</main>
@endsection('content')