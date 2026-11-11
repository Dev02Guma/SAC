<script>
  // ===== Usuarios (nueva UI) =====
  (function () {
    var USR = window.SAC_USUARIOS || [], RUTAS = (window.SAC_RUTAS || []).slice();
    var porId = {}; USR.forEach(function (u) { porId[u.id] = u; });
    var $id = function (x) { return document.getElementById(x); };
    var esc = function (v) { return $('<div>').text(v == null ? '' : String(v)).html(); };
    var TOKEN = "{{ csrf_token() }}";

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
        var txt = otros.length ? 'También: ' + otros.slice(0, 3).join(', ') + (otros.length > 3 ? ' +' + (otros.length - 3) : '') : 'Sin asignar';
        return '<li role="option" aria-selected="' + on + '" class="' + (on ? 'is-on' : '') + '" data-rt="' + esc(r) + '" tabindex="-1">' +
          '<span class="sac-rt-check" aria-hidden="true"></span><strong>' + esc(r) + '</strong><span class="sac-rt-otros">' + esc(txt) + '</span></li>';
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
    document.querySelectorAll('[data-usr-edit]').forEach(function (b) {
      b.addEventListener('click', function () { abrirModal(porId[b.getAttribute('data-usr-edit')]); });
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
      btn.disabled = true; btn.textContent = 'Guardando…';
      $.ajax({
        url: 'SaveUsuario', type: 'post', dataType: 'json',
        data: { _token: TOKEN, Estado: actual ? actual.id : 0, nombre: nombre, usuario: user, id_rol: rol,
                activo: $id('sacUActivo').checked ? 'S' : 'N', passwprd: p1, rutas_enviadas: 1, rutas: sel }
      }).done(function (r) {
        if (r && r.ok) { modal.hide(); Swal.fire({ icon: 'success', title: actual ? 'Usuario actualizado' : 'Usuario creado', timer: 1300, showConfirmButton: false }).then(function () { location.reload(); }); }
        else error((r && r.mensaje) || 'No se pudo guardar el usuario.');
      }).fail(function (x) {
        error((x.responseJSON && x.responseJSON.mensaje) || (x.status === 419 ? 'La sesión expiró. Recarga la página.' : 'No se pudo guardar el usuario.'));
      }).always(function () { btn.disabled = false; btn.textContent = txt; });
    });

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
      }).then(function (res) { if (res.isConfirmed) location.reload(); });
    }
    $id('sacUEliminar').addEventListener('click', function () { var u = actual; modal.hide(); setTimeout(function () { eliminar(u); }, 250); });
    document.querySelectorAll('[data-usr-del]').forEach(function (b) {
      b.addEventListener('click', function () { eliminar(porId[b.getAttribute('data-usr-del')]); });
    });
  })();
</script>
