    <script>
        // Modo oscuro (también en el login, que no tiene menú lateral)
        (function () {
            window.sacSetTheme = function (t) {
                document.documentElement.setAttribute('data-sac-theme', t);
                document.documentElement.classList.toggle('dark', t === 'dark');
                try { localStorage.setItem('sacTheme', t); localStorage.setItem('theme', t); } catch (e) {}
                document.querySelectorAll('[data-sac-theme-toggle]').forEach(function (b) {
                    b.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false');
                    b.setAttribute('aria-label', t === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
                    b.setAttribute('title', t === 'dark' ? 'Modo claro' : 'Modo oscuro');
                });
            };
            document.querySelectorAll('[data-sac-theme-toggle]').forEach(function (b) {
                b.addEventListener('click', function () {
                    sacSetTheme(document.documentElement.getAttribute('data-sac-theme') === 'dark' ? 'light' : 'dark');
                });
            });
            sacSetTheme(document.documentElement.getAttribute('data-sac-theme') || 'light');
        })();
    </script>
    <script>
        // Shell de la nueva UI: en escritorio colapsa el menú lateral (solo iconos, se recuerda);
        // en móvil lo abre como panel superpuesto.
        (function () {
            var app = document.getElementById('sacApp');
            if (!app) return;
            var btn = document.querySelector('[data-sac-toggle]');
            var mq = window.matchMedia('(max-width: 991.98px)');

            function syncAria() {
                if (!btn) return;
                var visible = mq.matches ? app.classList.contains('side-open') : !app.classList.contains('side-collapsed');
                btn.setAttribute('aria-expanded', visible ? 'true' : 'false');
            }
            function setOpen(open) { app.classList.toggle('side-open', open); syncAria(); }
            function setCollapsed(c) {
                app.classList.toggle('side-collapsed', c);
                try { localStorage.setItem('sacSideCollapsed', c ? '1' : '0'); } catch (e) {}
                syncAria();
                // Reajusta el ancho de las DataTables visibles tras la animación
                setTimeout(function () {
                    if (window.jQuery && $.fn.dataTable) $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }, 260);
            }
            function toggle() {
                if (mq.matches) setOpen(!app.classList.contains('side-open'));
                else setCollapsed(!app.classList.contains('side-collapsed'));
            }

            if (btn) btn.addEventListener('click', toggle);
            document.querySelectorAll('[data-sac-close], .sac-side .sac-ni').forEach(function (el) {
                el.addEventListener('click', function () { setOpen(false); });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setOpen(false);
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) { e.preventDefault(); toggle(); }
            });
            (mq.addEventListener ? mq.addEventListener('change', syncAria) : mq.addListener(syncAria));
            syncAria();

            // Animación de carga (filas "esqueleto") mientras llegan los datos de una tabla
            var SKEL = '<div class="sac-skel" role="status" aria-live="polite"><span class="visually-hidden">Cargando datos…</span>' +
                Array(6).join('<div class="sac-skel-row"><i class="sac-skel-dot"></i><i style="flex:3"></i><i></i><i></i><i></i></div>') + '</div>';
            var timers = {};
            window.sacTableLoading = function (sel, on) {
                var table = typeof sel === 'string' ? document.querySelector(sel) : sel;
                if (!table) return;
                var box = table.closest('.sac-tblbox') || table.parentNode;
                var skel = box.querySelector(':scope > .sac-skel');
                if (on) {
                    sacErrQuitar(box);
                    if (!skel) box.insertAdjacentHTML('afterbegin', SKEL);
                    box.classList.add('is-loading');
                    clearTimeout(timers[table.id]);
                    timers[table.id] = setTimeout(function () { window.sacTableLoading(table, false); }, 45000);
                } else {
                    if (skel) skel.remove();
                    box.classList.remove('is-loading');
                    clearTimeout(timers[table.id]);
                }
            };

            // Estado de error con "Reintentar" dentro del .sac-tblbox de la tabla (idempotente)
            var ICO_ERR = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
                '<circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>';
            function sacErrQuitar(box) {
                var err = box.querySelector(':scope > .sac-tbl-err');
                if (err) err.remove();
                box.classList.remove('has-error');
            }
            window.sacTableError = function (sel, mensaje, reintentar) {
                var table = typeof sel === 'string' ? document.querySelector(sel) : sel;
                if (!table) return;
                window.sacTableLoading(table, false);
                var box = table.closest('.sac-tblbox') || table.parentNode;
                sacErrQuitar(box);
                var div = document.createElement('div');
                div.className = 'sac-tbl-err';
                div.setAttribute('role', 'alert');
                div.innerHTML = '<span class="sac-tbl-err-ico">' + ICO_ERR + '</span>' +
                    '<p class="sac-tbl-err-msg"><span></span><span class="sac-tbl-err-hint">Revisa tu conexión e inténtalo de nuevo.</span></p>';
                div.querySelector('.sac-tbl-err-msg > span').textContent = mensaje || 'No se pudieron cargar los datos.';
                if (typeof reintentar === 'function') {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-falcon-default';
                    btn.textContent = 'Reintentar';
                    btn.addEventListener('click', function () {
                        window.sacTableLoading(table, true);
                        reintentar();
                    });
                    div.appendChild(btn);
                }
                box.insertBefore(div, box.firstChild);
                box.classList.add('has-error');
            };
            // Toda tabla vacía dentro de .sac-tblbox arranca "cargando" hasta que DataTables la inicializa
            document.querySelectorAll('.sac-tblbox > table[id]').forEach(function (t) {
                if (!t.tBodies.length || !t.tBodies[0].rows.length) window.sacTableLoading(t, true);
            });

            if (window.jQuery) {
                jQuery(document).on('init.dt draw.dt', function (e, settings) {
                    var $t = jQuery(settings.nTable);
                    // Si todas las cabeceras están vacías (p. ej. Pedidos) se oculta el thead
                    var vacio = $t.find('thead th').toArray().every(function (th) { return !jQuery.trim(jQuery(th).text()); });
                    $t.toggleClass('sac-thead-empty', vacio);
                    window.sacTableLoading(settings.nTable, false);
                    var box = settings.nTable.closest('.sac-tblbox') || settings.nTable.parentNode;
                    if (box) sacErrQuitar(box);
                });
            }
        })();
    </script>
