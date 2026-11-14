<script type="text/javascript">
    // Artículos favoritos: una sola tabla con todo el inventario (misma UI que Inventario del Home);
    // la estrella marca o desmarca el artículo como favorito sin recargar la página.
    var sacFav = { chip: 'todos' };
    var SAC_STAR = '<svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38a.56.56 0 0 1-.84.61L12 16.77l-4.73 2.86a.56.56 0 0 1-.84-.61l1.28-5.38-4.2-3.6a.56.56 0 0 1 .32-.99l5.52-.44 2.13-5.11Z"></path></svg>';

    function sacEsc(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
    function sacNum(v) { return parseFloat(numeral(v).value()) || 0; }
    function sacInvCant(row) { return sacNum(row['total']) + sacNum(row['005']); }
    function sacInvReglas(row) {
        if (!row.REGLAS || row.REGLAS == '0') return [];
        return String(row.REGLAS).split(',').map(function (r) { return $.trim(r); }).filter(Boolean);
    }
    function sacIniciales(txt) {
        var w = String(txt || '').replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ0-9 ]/g, ' ').split(/\s+/).filter(Boolean);
        return ((w[0] || '?').charAt(0) + (w[1] || '').charAt(0)).toUpperCase();
    }

    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== 'tbl_inventario') return true;
        if (sacFav.chip === 'fav') return row.FAV;
        if (sacFav.chip === 'nofav') return !row.FAV;
        return true;
    });

    // Filas sin disponible se muestran atenuadas, igual que en Inventario
    $('#tbl_inventario').on('draw.dt', function () {
        var api = $('#tbl_inventario').DataTable();
        api.rows({ page: 'current' }).every(function () {
            var sin = sacInvCant(this.data()) <= 0;
            $(this.node()).toggleClass('sac-inv-off', sin).attr('title', sin ? 'Sin disponible' : null);
        });
    });

    $(document).on('click', '[data-fav-chip]', function () {
        sacFav.chip = $(this).attr('data-fav-chip');
        $('[data-fav-chip]').removeClass('on').attr('aria-pressed', 'false');
        $(this).addClass('on').attr('aria-pressed', 'true');
        $('#tbl_inventario').DataTable().draw();
    });

    function sacFavContar() {
        var rows = $('#tbl_inventario').DataTable().rows().data().toArray();
        var fav = rows.filter(function (r) { return r.FAV; }).length;
        $('#sac_fav_n_todos').text(numeral(rows.length).format('0,0'));
        $('#sac_fav_n_fav').text(numeral(fav).format('0,0'));
        $('#sac_fav_n_nofav').text(numeral(rows.length - fav).format('0,0'));
    }

    var tbl_header_fav = [
        { "title": "Artículo", "data": "DESCRIPCION", "render": function (data, type, row) {
            if (type !== 'display') return (row.DESCRIPCION || '') + ' ' + row.ARTICULO + ' ' + (row.LABORATORIO || '');
            var rev = row.cuarentena != null ? ' <span class="badge badge-soft-warning ms-1">En revisión</span>' : '';
            return '<div class="sac-art">' +
                '<span class="sac-thumb">' +
                  (row.IMG_URL ? '<img src="' + sacEsc(row.IMG_URL) + '" alt="" loading="lazy" onerror="this.remove()">' : '') +
                  '<span aria-hidden="true">' + sacEsc(sacIniciales(row.DESCRIPCION)) + '</span>' +
                '</span>' +
                '<span class="sac-art-txt">' +
                  '<span><span class="sac-art-name">' + sacEsc(String(row.DESCRIPCION || '').toUpperCase()) + '</span>' + rev + '</span>' +
                  '<span class="sac-art-sub">' + sacEsc(row.ARTICULO) + ' · ' + sacEsc(row.UNIDAD_ALMACEN) + '</span>' +
                '</span></div>';
        }},
        { "title": "Disponible", "data": "total", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            var c = sacInvCant(row);
            return type === 'display' ? '<strong class="sac-num">' + numeral(c).format('0,0') + '</strong>' : c;
        }},
        { "title": "Precio farmacia", "data": "PRECIO_FARMACIA", "className": "text-end", "searchable": false, "render": function (data, type, row) {
            var p = sacNum(row.PRECIO_FARMACIA);
            return type === 'display' ? '<span class="sac-num">C$ ' + numeral(p).format('0,0.00') + '</span>' : p;
        }},
        { "title": "Bonificación", "data": "REGLAS", "defaultContent": "", "render": function (data, type, row) {
            var r = sacInvReglas(row);
            if (type !== 'display') return r.join(' ');
            return r.map(function (x) { return '<span class="sac-tag sac-tag-info">' + sacEsc(x) + '</span>'; }).join(' ');
        }},
        { "title": "Favorito", "data": "FAV", "className": "text-center", "searchable": false, "render": function (data, type, row) {
            if (type !== 'display') return row.FAV ? 1 : 0;
            var txt = row.FAV ? 'Quitar de favoritos' : 'Agregar a favoritos';
            return '<button type="button" class="sac-fav' + (row.FAV ? ' on' : '') + '" data-fav="' + sacEsc(row.ARTICULO) + '"' +
                ' aria-pressed="' + (row.FAV ? 'true' : 'false') + '" aria-label="' + txt + ': ' + sacEsc(row.DESCRIPCION) + '" title="' + txt + '">' + SAC_STAR + '</button>';
        }}
    ];

    function initTable(id, datos, Header) {
        $(id).DataTable({
            "data": datos,
            "destroy": true,
            "info": false,
            "bPaginate": true,
            "order": [[4, "desc"], [0, "asc"]],
            "lengthMenu": [[10, 20, 50, -1], [10, 20, 50, "Todas"]],
            "language": {
                "zeroRecords": "No hay resultados que coincidan",
                "paginate": { "first": "Primera", "last": "Última ", "next": "Siguiente", "previous": "Anterior" },
                "lengthMenu": "MOSTRAR _MENU_",
                "emptyTable": "-",
                "search": "BUSCAR"
            },
            'columns': Header,
        });
        $(id + "_length").hide();
        $(id + "_filter").hide();
    }

    function sacFavCargar() {
        if (window.sacTableLoading) sacTableLoading('#tbl_inventario', true);
        $("#id_loading").show();
        $.get("getArticulosFavoritos", function (data) {
            var fav = (data[0].ArticulosFav || []).map(function (r) { r.FAV = true; return r; });
            var resto = (data[0].Inventario || []).map(function (r) { r.FAV = false; return r; });
            initTable('#tbl_inventario', fav.concat(resto), tbl_header_fav);
            $('#tbl_inventario').DataTable().page.len(parseInt($('#tbl_select_inventario').val(), 10) || 10).draw();
            sacFavContar();
        }).always(function () {
            if (window.sacTableLoading) sacTableLoading('#tbl_inventario', false);
            $("#id_loading").hide();
        });
    }
    sacFavCargar();

    $("#tbl_select_inventario").change(function () {
        $('#tbl_inventario').DataTable().page.len(this.value).draw();
    });
    $('#tbl_buscar_inventario').on('keyup search', function () {
        $('#tbl_inventario').DataTable().search(this.value).draw();
    });

    // Marca o desmarca; actualiza solo la fila (sin recargar) y conserva la página actual
    $('#tbl_inventario').on('click', '[data-fav]', function () {
        var btn = $(this), api = $('#tbl_inventario').DataTable();
        var row = api.row(btn.closest('tr')), d = row.data();
        if (!d || btn.prop('disabled')) return;
        btn.prop('disabled', true);
        $.ajax({
            url: "AddFavs",
            type: 'post',
            data: { Articulo: d.ARTICULO, _token: "{{ csrf_token() }}" },
            success: function (response) {
                var ok = response && (response.original === true || typeof response.original === 'number');
                if (!ok) { Swal.fire('Oops', 'No se pudo actualizar el favorito.', 'error'); return; }
                d.FAV = !d.FAV;
                // 'page': redibuja sin reordenar, para que la fila no salte de lugar al marcarla
                row.data(d).invalidate().draw('page');
                sacFavContar();
            },
            error: function () { Swal.fire('Oops', 'No se pudo actualizar el favorito.', 'error'); },
            complete: function () { btn.prop('disabled', false); }
        });
    });

    // Ver imagen en grande
    $(document).on('click', '#tbl_inventario .sac-thumb img', function () {
        Swal.fire({ showCloseButton: true, showConfirmButton: false, imageUrl: $(this).attr('src'), imageAlt: '' });
    });
</script>
