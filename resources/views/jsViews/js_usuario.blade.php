<script>
  // ===== Usuarios (nueva UI) =====
  (function () {
    var USR = window.SAC_USUARIOS || [], RUTAS = (window.SAC_RUTAS || []).slice();
    var porId = {}; USR.forEach(function (u) { porId[u.id] = u; });
    var $id = function (x) { return document.getElementById(x); };
    // Escapa igual que la salida escapada de Blade (htmlspecialchars con ENT_QUOTES): válido en texto y en atributos
    var esc = function (v) { return $('<div>').text(v == null ? '' : String(v)).html().replace(/"/g, '&quot;').replace(/'/g, '&#039;'); };
    var TOKEN = "{{ csrf_token() }}";

    // ---------- Reglas de presentación (espejo de Usuario/Home.blade.php) ----------
    var ROLES = { '1': 'Admin', '2': 'SAC Sup.', '9': 'SAC', '10': 'Normal', '11': 'Schedule', '12': 'Devolución', '13': 'Supervisor', '14': 'Controlados' };
    function grupoRol(id) {
      id = String(id);
      if (id === '1') return 'admin';
      if (id === '2' || id === '9') return 'sac';
      if (id === '13') return 'sup';
      return 'otros';
    }
    function nombreRol(id) {
      id = String(id);
      if (ROLES[id]) return ROLES[id];
      // Roles fuera del mapa: el <select> del modal ya trae el nombre calculado por Blade
      var opts = document.querySelectorAll('#sacURol option');
      for (var i = 0; i < opts.length; i++) { if (opts[i].value === id) return opts[i].textContent.trim(); }
      return 'Sin rol';
    }
    // "SAC02 | REYNA ALVAREZ" -> "Reyna Alvarez"
    function titulo(t) {
      t = String(t == null ? '' : t).trim();
      if (t.indexOf('|') > -1) {
        var partes = t.split('|').map(function (s) { return s.trim(); }).filter(Boolean);
        if (partes.length) t = partes[partes.length - 1];
      }
      return t.toLowerCase().replace(/(^|[^\p{L}\p{M}'’.:])(\p{L})/gu, function (m, a, b) { return a + b.toUpperCase(); });
    }
    function iniciales(t) {
      var p = String(t == null ? '' : t).replace(/[^\p{L}\s]/gu, ' ').trim().split(/\s+/).filter(Boolean);
      var primera = function (s) { return s ? Array.from(s)[0] : ''; };
      return ((p[0] ? primera(p[0]) : '?') + primera(p[1])).toUpperCase() || '?';
    }
    var ICO_DEL = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M10 11v6M14 11v6"></path></svg>';
    // Marcado de una fila, idéntico al bucle de filas de la vista (solo se usa con permisos de administración)
    function filaHtml(u) {
      var rutas = u.rutas || [], on = u.activo === 'S';
      var buscar = (u.nombre + ' ' + u.username + ' ' + u.rol + ' ' + rutas.join(' ')).toLowerCase();
      var celdaRutas = rutas.length
        ? '<div class="sac-chips">' + rutas.slice(0, 10).map(function (r) { return '<span class="sac-chip-r">' + esc(r) + '</span>'; }).join('') +
          (rutas.length > 10 ? ' <span class="sac-chip-r is-more">+' + (rutas.length - 10) + ' más</span>' : '') + '</div>'
        : '<span class="sac-muted">Sin rutas asignadas</span>';
      return '<tr data-usr-id="' + esc(u.id) + '" data-grupo="' + esc(u.grupo) + '"' + (on ? '' : ' class="sac-usr-off" title="Usuario inactivo"') + ' data-buscar="' + esc(buscar) + '">' +
        '<td><span class="sac-avatar">' + esc(u.ini) + '<i class="sac-avatar-dot ' + (on ? 'is-ok' : 'is-off') + '" aria-hidden="true"></i></span></td>' +
        '<td><span class="sac-cli-name d-block">' + esc(u.nombre) + '</span> <span class="sac-cli-sub">' + esc(u.username) + '</span></td>' +
        '<td><div class="d-flex flex-column align-items-start gap-1"><span class="sac-tag sac-rol-' + esc(u.grupo) + '">' + esc(u.rol) + '</span></div></td>' +
        '<td>' + celdaRutas + '</td>' +
        '<td class="text-end"><div class="d-inline-flex gap-2">' +
          '<button type="button" class="btn btn-sm btn-falcon-default" data-usr-edit="' + esc(u.id) + '">Editar</button> ' +
          '<button type="button" class="sac-ib sac-ib-danger" data-usr-del="' + esc(u.id) + '" aria-label="Eliminar a ' + esc(u.nombre) + '" title="Eliminar">' + ICO_DEL + '</button>' +
        '</div></td></tr>';
    }
    function crearFila(u) { var tb = document.createElement('tbody'); tb.innerHTML = filaHtml(u); return tb.firstElementChild; }
    function filaDe(id) { return document.querySelector('#tbl_usuarios tbody tr[data-usr-id="' + String(id).replace(/["\\]/g, '\\$&') + '"]'); }
    function toast(t) { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: t, showConfirmButton: false, timer: 2200 }); }

    // ---------- Tabla: filtro por rol + búsqueda ----------
    var filtro = 'todos';
    function aplicarFiltro() {
      var q = ($id('sacUsrBuscar').value || '').toLowerCase().trim(), visibles = 0;
      document.querySelectorAll('#tbl_usuarios tbody tr').forEach(function (tr) {
        var ok = (filtro === 'todos' || tr.getAttribute('data-grupo') === filtro) && (!q || tr.getAttribute('data-buscar').indexOf(q) > -1);
        tr.hidden = !ok; if (ok) visibles++;
      });
      $id('sacUsrVacio').hidden = visibles > 0;
    }
    // Contadores de los filtros + subtítulo, y vuelve a aplicar filtro y búsqueda activos
    function refrescarLista() {
      var n = { todos: USR.length, admin: 0, sac: 0, sup: 0, otros: 0 };
      USR.forEach(function (u) { if (n[u.grupo] != null) n[u.grupo]++; });
      document.querySelectorAll('[data-usr-f]').forEach(function (b) {
        var s = b.querySelector('.sac-n'), k = b.getAttribute('data-usr-f');
        if (s && n[k] != null) s.textContent = n[k];
      });
      var sub = document.querySelector('.sac-head .sac-sub');
      if (sub) sub.textContent = USR.length + ' usuarios · roles y rutas asignadas';
      aplicarFiltro();
    }
    $id('sacUsrBuscar').addEventListener('input', aplicarFiltro);
    document.querySelectorAll('[data-usr-f]').forEach(function (b) {
      b.addEventListener('click', function () {
        filtro = b.getAttribute('data-usr-f');
        document.querySelectorAll('[data-usr-f]').forEach(function (x) { x.classList.toggle('active', x === b); x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        aplicarFiltro();
      });
    });

    var modalEl = $id('sacUsrModal');
    if (!modalEl) return; // sin permisos de administración
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var actual = null, sel = [];

    // Quién más tiene cada ruta (excluyendo al usuario que se edita)
    function otrosCon(ruta) {
      return USR.filter(function (u) { return (!actual || u.id !== actual.id) && u.rutas.indexOf(ruta) > -1; }).map(function (u) { return u.username; });
    }

    // ---------- Selector de rutas ----------
    var pop = $id('sacURtPop'), input = $id('sacURtInput');
    function abrir(v) { pop.hidden = !v; input.setAttribute('aria-expanded', v ? 'true' : 'false'); $id('sacURt').classList.toggle('is-open', v); if (v) pintarLista(); }
    function pintarChips() {
      $id('sacURtChips').innerHTML = sel.map(function (r) {
        return '<span class="sac-rt-chip">' + esc(r) + '<button type="button" data-rt-quitar="' + esc(r) + '" aria-label="Quitar ruta ' + esc(r) + '">×</button></span>';
      }).join('');
      var n = sel.length;
      $id('sacURutasCount').textContent = n + ' de ' + RUTAS.length + (RUTAS.length === 1 ? ' ruta seleccionada' : ' rutas seleccionadas');
      $id('sacURtSel').textContent = n + (n === 1 ? ' seleccionada' : ' seleccionadas');
      if (actual) $id('sacUsrSub').textContent = actual.username + ' · ' + actual.rol + ' · ' + n + (n === 1 ? ' ruta' : ' rutas');
    }
    function pintarLista() {
      var q = (input.value || '').toUpperCase().trim();
      var lista = RUTAS.filter(function (r) { return !q || r.indexOf(q) > -1; });
      $id('sacURtTotal').textContent = RUTAS.length + ' rutas';
      $id('sacURtList').innerHTML = lista.length ? lista.map(function (r) {
        var on = sel.indexOf(r) > -1, otros = otrosCon(r);
        var txt = otros.length ? 'También: ' + otros.slice(0, 4).join(', ') + (otros.length > 4 ? ' +' + (otros.length - 4) + ' más' : '') : 'Sin asignar';
        return '<li role="option" aria-selected="' + on + '" class="' + (on ? 'is-on' : '') + '" data-rt="' + esc(r) + '" tabindex="-1">' +
          '<span class="sac-rt-check" aria-hidden="true"></span><strong>' + esc(r) + '</strong><span class="sac-rt-otros"' + (otros.length ? ' title="' + esc('También la tienen: ' + otros.join(', ')) + '"' : '') + '>' + esc(txt) + '</span></li>';
      }).join('') : '<li class="sac-rt-none">No hay rutas que coincidan</li>';
    }
    function toggleRuta(r) {
      var i = sel.indexOf(r);
      if (i > -1) sel.splice(i, 1); else sel.push(r);
      sel.sort(function (a, b) { return a.localeCompare(b, 'es', { numeric: true }); });
      pintarChips(); pintarLista();
    }
    $id('sacURtList').addEventListener('mousedown', function (e) { e.preventDefault(); });
    $id('sacURtList').addEventListener('click', function (e) { var li = e.target.closest('[data-rt]'); if (li) toggleRuta(li.getAttribute('data-rt')); });
    $id('sacURtChips').addEventListener('click', function (e) { var b = e.target.closest('[data-rt-quitar]'); if (b) toggleRuta(b.getAttribute('data-rt-quitar')); });
    input.addEventListener('focus', function () { abrir(true); });
    input.addEventListener('input', function () { abrir(true); pintarLista(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var q = input.value.toUpperCase().trim();
        var match = RUTAS.filter(function (r) { return r.indexOf(q) > -1; });
        if (q && match.length) { toggleRuta(match[0]); input.value = ''; pintarLista(); }
      } else if (e.key === 'Escape') { e.stopPropagation(); abrir(false); }
      else if (e.key === 'Backspace' && !input.value && sel.length) { toggleRuta(sel[sel.length - 1]); }
    });
    $id('sacURtCaret').addEventListener('click', function () { abrir(pop.hidden); if (!pop.hidden) input.focus(); });
    $id('sacURtBox').addEventListener('click', function (e) { if (e.target === this) input.focus(); });
    $id('sacURtListo').addEventListener('click', function () { abrir(false); input.value = ''; });
    document.addEventListener('mousedown', function (e) { if (!pop.hidden && !$id('sacURt').contains(e.target)) abrir(false); });
    $id('sacURutasTodas').addEventListener('click', function () { sel = RUTAS.slice(); pintarChips(); pintarLista(); });
    $id('sacURutasNinguna').addEventListener('click', function () { sel = []; pintarChips(); pintarLista(); });

    // ---------- Estado ----------
    function pintarEstado() {
      $id('sacUActivoTxt').textContent = $id('sacUActivo').checked ? 'Activo · puede ingresar' : 'Inactivo · no puede ingresar';
    }
    $id('sacUActivo').addEventListener('change', pintarEstado);

    function error(m) { $id('sacUErr').textContent = m || ''; $id('sacUErr').hidden = !m; }

    // ---------- Abrir modal ----------
    function abrirModal(u) {
      actual = u || null; sel = u ? u.rutas.slice() : [];
      error(''); abrir(false); input.value = '';
      $id('sacUsrTitulo').textContent = u ? 'Editar usuario' : 'Nuevo usuario';
      $id('sacUsrAv').textContent = u ? u.ini : '+';
      $id('sacUNombre').value = u ? u.nombre : '';
      $id('sacUUser').value = u ? u.username : '';
      $id('sacURol').value = u ? u.id_rol : '';
      $id('sacUActivo').checked = u ? u.activo === 'S' : true;
      $id('sacUPass').value = ''; $id('sacUPass2').value = '';
      $id('sacUPass').placeholder = u ? 'Dejar vacío para mantener la actual' : 'Mínimo 8 caracteres';
      $id('sacUPassHint').textContent = u ? 'Mínimo 8 caracteres. Solo complétala si quieres restablecerla.' : 'Mínimo 8 caracteres. Obligatoria para un usuario nuevo.';
      $id('sacUEliminar').hidden = !u;
      $id('sacUGuardar').textContent = u ? 'Guardar cambios' : 'Crear usuario';
      if (!u) $id('sacUsrSub').textContent = 'Completa los datos y asigna sus rutas';
      pintarEstado(); pintarChips();
      modal.show();
    }
    modalEl.addEventListener('shown.bs.modal', function () { $id('sacUNombre').focus(); });
    // Delegación: sirve también para filas creadas o reemplazadas sin recargar
    $id('tbl_usuarios').addEventListener('click', function (e) {
      var b = e.target.closest('[data-usr-edit], [data-usr-del]');
      if (!b) return;
      if (b.hasAttribute('data-usr-edit')) abrirModal(porId[b.getAttribute('data-usr-edit')]);
      else eliminar(porId[b.getAttribute('data-usr-del')]);
    });
    var nuevo = $id('sacUsrNuevo'); if (nuevo) nuevo.addEventListener('click', function () { abrirModal(null); });

    // ---------- Guardar ----------
    $id('sacUsrForm').addEventListener('submit', function (e) {
      e.preventDefault(); error('');
      var nombre = $id('sacUNombre').value.trim(), user = $id('sacUUser').value.trim().toUpperCase(), rol = $id('sacURol').value;
      var p1 = $id('sacUPass').value, p2 = $id('sacUPass2').value;
      if (!nombre) { $id('sacUNombre').focus(); return error('Escribe el nombre completo.'); }
      if (!user) { $id('sacUUser').focus(); return error('Escribe el nombre de usuario.'); }
      if (!rol) { $id('sacURol').focus(); return error('Selecciona el rol.'); }
      if (!actual && !p1) { $id('sacUPass').focus(); return error('La contraseña es obligatoria para un usuario nuevo.'); }
      if (p1 && p1.length < 8) { $id('sacUPass').focus(); return error('La contraseña debe tener al menos 8 caracteres.'); }
      if (p1 !== p2) { $id('sacUPass2').focus(); return error('Las contraseñas no coinciden.'); }

      var btn = $id('sacUGuardar'), txt = btn.textContent;
      var editado = actual, activo = $id('sacUActivo').checked ? 'S' : 'N', rutas = sel.slice();
      btn.disabled = true; btn.textContent = 'Guardando…';
      $.ajax({
        url: 'SaveUsuario', type: 'post', dataType: 'json',
        data: { _token: TOKEN, Estado: editado ? editado.id : 0, nombre: nombre, usuario: user, id_rol: rol,
                activo: activo, passwprd: p1, rutas_enviadas: 1, rutas: rutas }
      }).done(function (r) {
        if (!r || !r.ok) return error((r && r.mensaje) || 'No se pudo guardar el usuario.');
        var id = editado ? editado.id : r.id;
        if (id == null || id === '') { location.reload(); return; } // respaldo: el backend no devolvió el id
        aplicarGuardado(editado, { id: id, nombre: nombre, username: user, id_rol: rol, activo: activo, rutas: rutas });
        modal.hide();
        toast(editado ? 'Usuario actualizado' : 'Usuario creado');
      }).fail(function (x) {
        error((x.responseJSON && x.responseJSON.mensaje) || (x.status === 419 ? 'La sesión expiró. Recarga la página.' : 'No se pudo guardar el usuario.'));
      }).always(function () { btn.disabled = false; btn.textContent = txt; });
    });

    // Actualiza USR/porId (= window.SAC_USUARIOS) y la fila de la tabla tras guardar
    function aplicarGuardado(previo, d) {
      var nom = titulo(d.nombre), rol = String(d.id_rol);
      var u = previo || {};
      u.id = d.id; u.nombre = nom; u.username = d.username; u.id_rol = rol; u.rol = nombreRol(rol);
      u.grupo = grupoRol(rol); u.activo = d.activo === 'N' ? 'N' : 'S'; u.ini = iniciales(nom); u.rutas = d.rutas.slice();
      if (!previo) USR.push(u);
      porId[u.id] = u;
      window.SAC_USUARIOS = USR;

      var tr = crearFila(u), viejo = filaDe(u.id), tbody = document.querySelector('#tbl_usuarios tbody');
      if (viejo) viejo.parentNode.replaceChild(tr, viejo);
      else {
        // Nuevo: insertar en orden alfabético por nombre
        var antes = null, filas = tbody.querySelectorAll('tr');
        for (var i = 0; i < filas.length; i++) {
          var o = porId[filas[i].getAttribute('data-usr-id')];
          if (o && o !== u && o.nombre.localeCompare(u.nombre, 'es', { sensitivity: 'base' }) > 0) { antes = filas[i]; break; }
        }
        tbody.insertBefore(tr, antes);
      }
      refrescarLista();
    }

    // Quita al usuario de los datos y su fila de la tabla tras eliminar
    function quitarUsuario(u) {
      var i = USR.indexOf(u);
      if (i > -1) USR.splice(i, 1);
      delete porId[u.id];
      window.SAC_USUARIOS = USR;
      if (actual === u) actual = null;
      var tr = filaDe(u.id);
      var reducir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (!tr || reducir) { if (tr) tr.remove(); refrescarLista(); return; }
      tr.style.transition = 'opacity .2s ease';
      tr.style.opacity = '0';
      setTimeout(function () { tr.remove(); refrescarLista(); }, 200);
    }

    // ---------- Eliminar ----------
    function eliminar(u) {
      if (!u) return;
      Swal.fire({
        title: '¿Eliminar a ' + u.nombre + '?',
        text: 'El usuario ya no podrá ingresar y se liberarán sus ' + u.rutas.length + ' rutas.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#B91C1C', reverseButtons: true, showLoaderOnConfirm: true,
        preConfirm: function () {
          return $.ajax({ url: 'DeleteUsuario', type: 'post', dataType: 'json', data: { _token: TOKEN, id: u.id } })
            .then(function (r) { if (!r || !r.ok) throw new Error((r && r.mensaje) || 'No se pudo eliminar.'); return r; })
            .catch(function (x) { Swal.showValidationMessage((x && x.responseJSON && x.responseJSON.mensaje) || x.message || 'No se pudo eliminar.'); });
        },
        allowOutsideClick: function () { return !Swal.isLoading(); }
      }).then(function (res) {
        if (res.isConfirmed && res.value && res.value.ok) { quitarUsuario(u); toast('Usuario eliminado'); }
      });
    }
    $id('sacUEliminar').addEventListener('click', function () { var u = actual; modal.hide(); setTimeout(function () { eliminar(u); }, 250); });
  })();
</script>
