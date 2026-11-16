<script type="text/javascript">

  var Selectors = {
    MODAL_CALENDAR: '#ShowEventModal',
  };

  // ===== Utilidades =====
  var EST_MES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
  var EST_MES_EN = { january:'enero', february:'febrero', march:'marzo', april:'abril', may:'mayo', june:'junio', july:'julio', august:'agosto', september:'septiembre', october:'octubre', november:'noviembre', december:'diciembre' };

  // El backend manda varios valores ya formateados ("C$ 2,462,066", "26 %"): se lee solo el número
  function estNum(v) {
    if (typeof v === 'number') return isFinite(v) ? v : 0;
    var n = parseFloat(String(v == null ? '' : v).replace(/[^0-9.\-]/g, ''));
    return isNaN(n) ? 0 : n;
  }
  function estMoney(v, dec) { return 'C$ ' + numeral(estNum(v)).format(dec ? '0,0.00' : '0,0'); }
  function estPct(v) { return numeral(estNum(v)).format('0,0') + '%'; }
  function estEsc(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
  function estAbbr(n) {
    n = estNum(n); var a = Math.abs(n);
    if (a >= 1e6) return (n / 1e6).toFixed(a >= 1e7 ? 1 : 2).replace(/\.?0+$/, '') + ' M';
    if (a >= 1e3) return Math.round(n / 1e3) + ' K';
    return String(Math.round(n));
  }
  function estTitulo(s) {
    return String(s || '').toLowerCase().replace(/(^|[\s.\-])([a-záéíóúñ])/g, function (m, a, b) { return a + b.toUpperCase(); });
  }
  function estIniciales(s) {
    var p = String(s || '').trim().split(/\s+/).filter(Boolean);
    return ((p[0] || '?').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : '')).toUpperCase();
  }
  function estVar(nombre, defecto) {
    var v = getComputedStyle(document.body).getPropertyValue(nombre);
    return $.trim(v) || defecto;
  }
  function estParse(iso) {
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec($.trim(iso || ''));
    return m ? { a: +m[1], m: +m[2] - 1, d: +m[3] } : null;
  }
  function estRangoTexto(d1, d2) {
    var a = estParse(d1), b = estParse(d2) || a;
    if (!a) return 'Venta, cumplimiento y plan de trabajo por vendedor';
    var t = function (x, anio) { return x.d + ' ' + EST_MES[x.m] + (anio ? ' ' + x.a : ''); };
    if (a.a === b.a && a.m === b.m && a.d === b.d) return 'Del ' + t(a, true);
    return 'Del ' + t(a, a.a !== b.a) + ' al ' + t(b, true);
  }
  // Compara el cumplimiento con el avance esperado del mes
  function estRitmo(cumpl, esperado) {
    if (!(esperado > 0)) return { cls: 'sac-tag-neutral', txt: 'Sin referencia' };
    if (cumpl >= esperado) return { cls: 'sac-tag-ok', txt: 'En ritmo' };
    if (cumpl >= esperado * 0.85) return { cls: 'sac-tag-warn', txt: 'Cerca del ritmo' };
    return { cls: 'sac-tag-bad', txt: 'Bajo el ritmo' };
  }

  // ===== Estado =====
  var est = { req: 0, d1: '', d2: '', esperado: 0, rutas: [], dias: [], rutasVenta: [], charts: {} };

  const startOfMonth = moment().startOf('month').format('YYYY-MM-DD');
  const endOfMonth   = moment().format('YYYY-MM-DD');
  var labelRange = startOfMonth + ' to ' + endOfMonth;
  $('#id_range_select').val(labelRange);

  $('#id_range_select').on('change', function () {
    var f = String($(this).val() || '').split('to').map(function (x) { return $.trim(x); });
    if (f.length >= 2 && f[0] && f[1]) RangeStat(f[0], f[1]);
  });

  // Calendarios Desde / Hasta (mismo patrón que Pedidos)
  (function () {
    var desde = document.getElementById('sacEstDesde'), hasta = document.getElementById('sacEstHasta'), oculto = document.getElementById('id_range_select');
    if (!desde || !hasta || !window.flatpickr) return;
    var ES = {
      weekdays: { shorthand: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'], longhand: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] },
      months: { shorthand: EST_MES, longhand: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] },
      firstDayOfWeek: 1, ordinal: function () { return ''; }
    };
    var fpDesde, fpHasta;
    function consultar() {
      var a = fpDesde.selectedDates[0], b = fpHasta.selectedDates[0];
      if (!a || !b) return;
      var v = flatpickr.formatDate(a, 'Y-m-d') + ' to ' + flatpickr.formatDate(b, 'Y-m-d');
      if (v !== oculto.value) { oculto.value = v; $(oculto).trigger('change'); }
    }
    var base = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'j M Y', altInputClass: 'form-control', locale: ES, disableMobile: true };
    fpDesde = flatpickr(desde, $.extend({}, base, {
      defaultDate: startOfMonth, maxDate: endOfMonth,
      onChange: function (d) {
        if (fpHasta) {
          fpHasta.set('minDate', d[0] || null);
          if (d[0] && fpHasta.selectedDates[0] && fpHasta.selectedDates[0] < d[0]) fpHasta.setDate(d[0], false);
        }
        consultar();
      }
    }));
    fpHasta = flatpickr(hasta, $.extend({}, base, {
      defaultDate: endOfMonth, minDate: startOfMonth, position: 'auto right',
      onChange: function (d) { fpDesde.set('maxDate', d[0] || null); consultar(); }
    }));
  })();

  RangeStat(startOfMonth, endOfMonth);

  // ===== Carga =====
  function RangeStat(D1, D2) {
    D1 = $.trim(D1); D2 = $.trim(D2);
    var req = ++est.req;
    est.d1 = D1; est.d2 = D2;
    $('#sacEstSub').text(estRangoTexto(D1, D2));
    $('#sacEstLoading').show();
    $('#id_rutas').html('<p class="sac-est-empty">Cargando rutas…</p>');

    $.get('getMiProgreso/' + D1 + '/' + D2, function (data) {
      if (req !== est.req) return;
      var r = (data && data[0]) || {};
      Estadisticas(r.Estadistica || {});
      setBarVentasMes(r.dtaVentasMes || []);
      setBarVentasRutas(r.dtaVentasRutas || []);
    }).fail(function () {
      if (req !== est.req) return;
      $('#id_rutas').html('<p class="sac-est-empty">No se pudieron cargar las estadísticas. <button type="button" class="btn btn-link" id="sacEstReintentar">Reintentar</button></p>');
    }).always(function () {
      if (req === est.req) $('#sacEstLoading').hide();
    });
  }
  $(document).on('click', '#sacEstReintentar', function () { RangeStat(est.d1, est.d2); });

  // ===== Resumen =====
  function Estadisticas(Data) {
    var meta = estNum(Data.Venta_Meta), real = estNum(Data.Venta_Real), cumpl = estNum(Data.Venta_Porc);
    var esperado = estNum(Data.Dias_porcent);
    var cliMeta = estNum(Data.Cliente_Meta), cliReal = estNum(Data.Cliente_Real), cliPorc = estNum(Data.Cliente_Porc);
    est.esperado = esperado;

    $('#id_Venta_Real').text(estMoney(real, true));
    $('#id_Venta_Meta').text(estMoney(meta, true));
    $('#id_Venta_Porc').text(estPct(cumpl));
    $('#sacEstBarVenta').css('width', Math.min(100, cumpl) + '%');
    $('#sacEstMarkVenta').css('left', Math.min(100, esperado) + '%').toggle(esperado > 0);
    $('#sacEstEsperado').text(estPct(esperado));
    var r = estRitmo(cumpl, esperado);
    $('#sacEstRitmo').attr('class', 'sac-tag ' + r.cls).text(r.txt).prop('hidden', !(meta > 0));
    $('#sacEstBarVenta').toggleClass('is-ok', cumpl >= esperado && esperado > 0);

    $('#id_Cliente_Real').text(numeral(cliReal).format('0,0'));
    $('#id_Cliente_Meta').text(cliMeta > 0 ? numeral(cliMeta).format('0,0') : 'sin meta');
    $('#id_Cliente_Porc').text(cliMeta > 0 ? estPct(cliPorc) : '');
    $('#sacEstCliNota').contents().last()[0].nodeValue = cliMeta > 0 ? ' de la meta' : 'Sin meta de clientes';
    $('#sacEstBarCli').css('width', Math.min(100, cliPorc) + '%');

    $('#id_Dias_porcent').text(estPct(esperado));
    $('#sacEstBarDias').css('width', Math.min(100, esperado) + '%');
    $('#id_Dias_Habiles').text(numeral(estNum(Data.Dias_Habiles)).format('0,0'));
    $('#id_Dias_Facturados').text(numeral(estNum(Data.Dias_Facturados)).format('0,0'));

    $('#id_Venta_Actu').text(estMoney(Data.Venta_Actu, true));
    $('#id_Venta_Week').text(estMoney(Data.Venta_Week, true));
    var lbl = String(Data.Venta_Week_Label || '').replace(/:\s*$/, '').replace(/[A-Za-z]{3,}$/, function (m) { return EST_MES_EN[m.toLowerCase()] || m; });
    $('#id_Venta_Week_Label').text(lbl);

    est.rutas = [];
    $.each(Data, function (i, d) {
      if (!d || d.VENDEDOR === undefined) return;
      var metaCli = estNum(d.META_CLIENTE), cli = estNum(d.CLIENTE);
      est.rutas.push({
        ruta: d.VENDEDOR, nombre: estTitulo(d.NOMBRE), zona: d.ZONA || '',
        meta: estNum(d.META_RUTA), real: estNum(d.MesActual), cumpl: estNum(d.RUTA_CUMPLI),
        tendencia: estNum(d.TENDENCIA), cli: cli, metaCli: metaCli, cobertura: metaCli > 0 ? cli / metaCli * 100 : null,
        sku: estNum(d.SKU), hoy: estNum(d.DiaActual), semana: estNum(d.SaleWeek),
        sac: estNum(d.SAC), ejec: estNum(d.EJEC), ds: estNum(d.DS)
      });
    });
    $('#sacEstNRutas').text(est.rutas.length || '');
    pintarRutas();
  }

  // ===== Rutas =====
  function pintarRutas() {
    var q = $.trim($('#sacEstBuscarRuta').val() || '').toLowerCase();
    var orden = $('#sacEstOrden').val() || 'cumpl';
    var lista = est.rutas.filter(function (r) {
      return !q || (r.ruta + ' ' + r.nombre + ' ' + r.zona).toLowerCase().indexOf(q) !== -1;
    });
    lista.sort(function (a, b) {
      if (orden === 'venta') return b.real - a.real;
      if (orden === 'ruta') return a.ruta.localeCompare(b.ruta);
      return b.cumpl - a.cumpl;
    });
    if (!est.rutas.length) { $('#id_rutas').html('<p class="sac-est-empty">No hay rutas con ventas en este periodo</p>'); return; }
    if (!lista.length) { $('#id_rutas').html('<p class="sac-est-empty">Ninguna ruta coincide con la búsqueda</p>'); return; }

    var esperado = est.esperado;
    $('#id_rutas').html(lista.map(function (r) {
      var rit = estRitmo(r.cumpl, esperado);
      var proyCls = r.tendencia >= 100 ? 'sac-est-up' : (r.tendencia >= 85 ? 'sac-est-warn' : 'sac-est-down');
      var dato = function (k, v) { return '<div><dt>' + k + '</dt><dd class="sac-num">' + v + '</dd></div>'; };
      return '' +
        '<article class="sac-est-ruta">' +
          '<header class="sac-est-ruta-head">' +
            '<span class="sac-est-av" aria-hidden="true">' + estEsc(estIniciales(r.nombre)) + '</span>' +
            '<div class="sac-est-ruta-id">' +
              '<div class="sac-est-ruta-name">' + estEsc(r.nombre) + '</div>' +
              '<div class="sac-cli-sub">' + estEsc(r.ruta) + (r.zona ? ' · ' + estEsc(r.zona) : '') + '</div>' +
            '</div>' +
            (r.meta > 0 ? '<span class="sac-tag ' + rit.cls + '" title="' + rit.txt + '">' + estPct(r.cumpl) + '</span>' : '<span class="sac-tag sac-tag-neutral">Sin meta</span>') +
          '</header>' +
          '<div class="sac-est-ruta-body">' +
            '<div class="sac-est-metric">' +
              '<div class="sac-est-metric-top"><span class="sac-kl">Venta</span><span class="sac-muted">Meta ' + estMoney(r.meta) + '</span></div>' +
              '<div class="sac-est-big sac-num">' + estMoney(r.real) + '</div>' +
              '<div class="sac-bar sac-est-bar"><span class="' + (r.cumpl >= esperado && esperado > 0 ? 'is-ok' : '') + '" style="width:' + Math.min(100, r.cumpl) + '%"></span>' +
                (esperado > 0 ? '<i class="sac-est-mark" style="left:' + Math.min(100, esperado) + '%" title="Óptimo: ' + estPct(esperado) + '"></i>' : '') + '</div>' +
              '<div class="sac-est-metric-top"><span class="sac-muted">Tendencia <strong class="' + proyCls + '">' + estPct(r.tendencia) + '</strong></span>' +
                (esperado > 0 ? '<span class="sac-muted">Óptimo <strong class="sac-est-strong">' + estPct(esperado) + '</strong></span>' : '') + '</div>' +
            '</div>' +
            '<div class="sac-est-metric">' +
              '<div class="sac-est-metric-top"><span class="sac-kl">Clientes facturados</span><span class="sac-muted">' + (r.metaCli > 0 ? 'Meta ' + numeral(r.metaCli).format('0,0') : 'Sin meta') + '</span></div>' +
              '<div class="sac-est-big sac-num">' + numeral(r.cli).format('0,0') + (r.cobertura != null ? ' <span class="sac-kv-of">' + estPct(r.cobertura) + '</span>' : '') + '</div>' +
              (r.cobertura != null ? '<div class="sac-bar"><span style="width:' + Math.min(100, r.cobertura) + '%; background: var(--sac-teal)"></span></div>' : '') +
            '</div>' +
            '<dl class="sac-est-grid">' +
              dato('Hoy', estMoney(r.hoy)) +
              dato('Esta semana', estMoney(r.semana)) +
              dato('SKUs vendidos', numeral(r.sku).format('0,0')) +
              dato('Pedidos SAC', estMoney(r.sac)) +
              dato('Pedidos vendedor', estMoney(r.ejec)) +
              dato('Venta por cliente', estMoney(r.ds)) +
            '</dl>' +
          '</div>' +
        '</article>';
    }).join(''));
  }
  $('#sacEstBuscarRuta').on('input', pintarRutas);
  $('#sacEstOrden').on('change', pintarRutas);

  // ===== Gráficos =====
  function setBarVentasMes(Data) {
    var a = estParse(est.d1), b = estParse(est.d2);
    var mismoMes = a && b && a.a === b.a && a.m === b.m;
    est.dias = Data.map(function (d) { return { dia: estNum(d.DIA), monto: estNum(d.MONTO) }; });
    if (mismoMes) est.dias.sort(function (x, y) { return x.dia - y.dia; });
    est.diasMes = mismoMes ? a.m : null;
    graficoDias();
  }
  function setBarVentasRutas(Data) {
    est.rutasVenta = Data.map(function (d) {
      return { ruta: d.VENDEDOR, nombre: estTitulo(d.NOMBRE), zona: d.ZONA || '', monto: estNum(d.MONTO) };
    }).sort(function (x, y) { return y.monto - x.monto; });
    graficoRutas();
  }

  function estChart(sel) {
    var el = document.querySelector(sel);
    if (!el || !window.echarts) return null;
    var c = echarts.getInstanceByDom(el) || echarts.init(el);
    est.charts[sel] = c;
    return c;
  }
  function estTooltip(titulo, valor, extra) {
    return '<div class="sac-est-tip"><div class="sac-est-tip-t">' + estEsc(titulo) + '</div><div class="sac-est-tip-v">' + valor + '</div>' + (extra ? '<div class="sac-est-tip-x">' + extra + '</div>' : '') + '</div>';
  }
  function estColores() {
    return {
      primary: estVar('--sac-primary', '#2B3B9E'), ink: estVar('--sac-ink', '#15204F'), muted: estVar('--sac-muted', '#4A5878'),
      border: estVar('--sac-border-soft', '#E8EDF5'), warn: estVar('--sac-warn', '#D98A00'), surface: estVar('--sac-surface', '#FFFFFF'),
      font: estVar('--sac-font', 'Archivo, sans-serif')
    };
  }

  function graficoDias() {
    var c = estChart('.echart-sale-VentasDelMes'); if (!c) return;
    var datos = est.dias, col = estColores();
    var total = datos.reduce(function (s, d) { return s + d.monto; }, 0);
    var prom = datos.length ? total / datos.length : 0;
    var mejor = datos.reduce(function (m, d) { return !m || d.monto > m.monto ? d : m; }, null);
    var etiqueta = function (d) { return est.diasMes != null ? d.dia + ' ' + EST_MES[est.diasMes] : 'Día ' + d.dia; };
    $('#sacEstDiaVacio').prop('hidden', datos.length > 0);
    $('#sacEstDiaRes').html(datos.length
      ? 'Total <strong class="sac-num">' + estMoney(total) + '</strong> · Promedio diario <strong class="sac-num">' + estMoney(prom) + '</strong> · Mejor día <strong>' + etiqueta(mejor) + '</strong> (' + estMoney(mejor.monto) + ')'
      : 'Sin ventas en el periodo');
    if (!datos.length) { c.clear(); return; }
    c.setOption({
      textStyle: { fontFamily: col.font },
      grid: { left: 8, right: 16, top: 28, bottom: 8, containLabel: true },
      tooltip: {
        trigger: 'axis', axisPointer: { type: 'shadow', shadowStyle: { color: 'rgba(43,59,158,.06)' } },
        backgroundColor: col.surface, borderColor: col.border, borderWidth: 1, padding: 0, extraCssText: 'box-shadow: 0 8px 24px -8px rgba(21,32,79,.25); border-radius: 10px;',
        formatter: function (p) { var d = datos[p[0].dataIndex]; return estTooltip(etiqueta(d), estMoney(d.monto, true), prom ? numeral((d.monto / prom - 1) * 100).format('+0') + '% vs. promedio' : ''); }
      },
      xAxis: {
        type: 'category', data: datos.map(etiqueta),
        axisLine: { lineStyle: { color: col.border } }, axisTick: { show: false },
        axisLabel: { color: col.muted, fontSize: 12 }
      },
      yAxis: {
        type: 'value', splitNumber: 4,
        splitLine: { lineStyle: { color: col.border, type: 'dashed' } },
        axisLabel: { color: col.muted, fontSize: 12, formatter: function (v) { return estAbbr(v); } }
      },
      series: [{
        type: 'bar', data: datos.map(function (d) { return d.monto; }), barMaxWidth: 34,
        itemStyle: { color: col.primary, borderRadius: [6, 6, 0, 0] },
        emphasis: { itemStyle: { color: estVar('--sac-primary-hover', '#1B2766') } },
        label: { show: datos.length <= 16, position: 'top', color: col.ink, fontSize: 11, formatter: function (p) { return estAbbr(p.value); } },
        markLine: datos.length ? {
          symbol: 'none', silent: true, data: [{ yAxis: prom }],
          lineStyle: { color: col.warn, type: 'dashed', width: 1.5 },
          label: { position: 'insideEndTop', color: col.warn, fontSize: 11, formatter: 'Promedio ' + estAbbr(prom) }
        } : undefined
      }]
    }, true);
  }

  function graficoRutas() {
    var el = document.querySelector('.echart-sale-VentasMesRutas'); if (!el) return;
    var datos = est.rutasVenta, col = estColores();
    var total = datos.reduce(function (s, d) { return s + d.monto; }, 0);
    el.style.height = Math.max(240, datos.length * 34 + 30) + 'px';
    var c = estChart('.echart-sale-VentasMesRutas'); if (!c) return;
    c.resize();
    $('#sacEstRutaVacio').prop('hidden', datos.length > 0);
    $('#sacEstRutaRes').html(datos.length
      ? numeral(datos.length).format('0,0') + (datos.length === 1 ? ' ruta' : ' rutas') + ' · Total <strong class="sac-num">' + estMoney(total) + '</strong>'
      : 'Sin ventas en el periodo');
    if (!datos.length) { c.clear(); return; }
    var inv = datos.slice().reverse(); // eCharts dibuja de abajo hacia arriba: la mayor queda arriba
    c.setOption({
      textStyle: { fontFamily: col.font },
      grid: { left: 8, right: 70, top: 8, bottom: 8, containLabel: true },
      tooltip: {
        trigger: 'axis', axisPointer: { type: 'shadow', shadowStyle: { color: 'rgba(43,59,158,.06)' } },
        backgroundColor: col.surface, borderColor: col.border, borderWidth: 1, padding: 0, extraCssText: 'box-shadow: 0 8px 24px -8px rgba(21,32,79,.25); border-radius: 10px;',
        formatter: function (p) { var d = inv[p[0].dataIndex]; return estTooltip(d.ruta + ' · ' + d.nombre, estMoney(d.monto, true), (d.zona ? d.zona + ' · ' : '') + (total ? numeral(d.monto / total * 100).format('0.0') + '% del total' : '')); }
      },
      xAxis: { type: 'value', splitNumber: 4, splitLine: { lineStyle: { color: col.border, type: 'dashed' } }, axisLabel: { color: col.muted, fontSize: 12, formatter: function (v) { return estAbbr(v); } } },
      yAxis: {
        type: 'category', data: inv.map(function (d) { return d.ruta + '  ' + d.nombre; }),
        axisLine: { show: false }, axisTick: { show: false },
        axisLabel: { color: col.ink, fontSize: 12, width: 190, overflow: 'truncate' }
      },
      series: [{
        type: 'bar', data: inv.map(function (d) { return d.monto; }), barMaxWidth: 20,
        itemStyle: { color: col.primary, borderRadius: [0, 6, 6, 0] },
        emphasis: { itemStyle: { color: estVar('--sac-primary-hover', '#1B2766') } },
        label: { show: true, position: 'right', color: col.ink, fontSize: 11, formatter: function (p) { return 'C$ ' + estAbbr(p.value); } }
      }]
    }, true);
  }

  // Redimensiona al cambiar de pestaña o de tamaño de ventana
  function estResize() { Object.keys(est.charts).forEach(function (k) { est.charts[k] && est.charts[k].resize(); }); }
  $('#crm-revenue-chart-tab a[data-bs-toggle="tab"]').on('shown.bs.tab', estResize);
  $(window).on('resize', estResize);
  // Al cambiar entre tema claro y oscuro se repintan con los colores nuevos
  if (window.MutationObserver) {
    new MutationObserver(function () { graficoDias(); graficoRutas(); })
      .observe(document.documentElement, { attributes: true, attributeFilter: ['data-sac-theme'] });
  }

</script>
